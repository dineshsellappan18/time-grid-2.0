<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\TG\Business\Token as BusinessToken;
use App\TG\ICalTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JavaScript;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;

class BusinessAgendaController extends Controller
{
    private Concierge $concierge;
    private ICalTokenService $tokenService;

    public function __construct(Concierge $concierge, ICalTokenService $tokenService)
    {
        $this->concierge = $concierge;
        $this->tokenService = $tokenService;

        parent::__construct();
    }

    public function getIndex(Business $business)
    {
        Log::info('agenda.index', [
            'actor'     => auth()->id(),
            'resource'  => 'business',
            'operation' => 'view_agenda',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->authorize('manage', $business);

        $appointments = $business->bookings()
            ->with(['contact', 'service'])
            ->unarchived()
            ->orderBy('start_at')
            ->get();

        $viewKey = $appointments->isEmpty()
            ? 'manager.businesses.appointments.empty'
            : "manager.businesses.appointments.{$business->strategy}.index";

        $user = auth()->user();

        return view($viewKey, compact('business', 'appointments', 'user'));
    }

    public function getShow(Business $business, Appointment $appointment)
    {
        Log::info('agenda.show', [
            'actor'     => auth()->id(),
            'resource'  => 'appointment',
            'operation' => 'view_detail',
            'context'   => ['business_id' => $business->id, 'appointment_id' => $appointment->id],
        ]);

        $this->authorize('manage', $business);

        $appointment->load(['contact', 'service', 'humanresource']);

        $user = auth()->user();

        return view('manager.businesses.appointments.show', compact('business', 'appointment', 'user'));
    }

    public function getCalendar(Business $business)
    {
        Log::info('agenda.calendar', [
            'actor'     => auth()->id(),
            'resource'  => 'business',
            'operation' => 'view_calendar',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->authorize('manage', $business);

        $appointments = $business->bookings()
            ->with(['contact', 'service', 'humanresource'])
            ->active()
            ->get();

        $jsAppointments = [];

        foreach ($appointments as $appointment) {
            $contactName = trim(($appointment->contact->firstname ?? '') . ' ' . ($appointment->contact->lastname ?? '')) ?: 'Unknown';
            $serviceName = $appointment->service->name ?? 'Unknown';
            $assignee = $appointment->humanresource->name ?? null;

            $jsAppointments[] = [
                'id'        => $appointment->id,
                'title'     => $contactName . ' / ' . $serviceName,
                'color'     => $appointment->service->color ?? '#6366f1',
                'start'     => $appointment->start_at->copy()->timezone($business->timezone)->toIso8601String(),
                'end'       => $appointment->finish_at->copy()->timezone($business->timezone)->toIso8601String(),
                'extendedProps' => [
                    'appointmentId' => $appointment->id,
                    'contact'       => $contactName,
                    'service'       => $serviceName,
                    'assignee'      => $assignee,
                    'status'        => $appointment->statusLabel,
                    'comments'      => $appointment->comments,
                    'detailUrl'     => route('manager.business.agenda.show', [$business, $appointment]),
                ],
            ];
        }

        $slotDuration = $appointments->count() > 5 ? '0:15' : '0:30';

        $icalURL = $this->generateICalURL($business);

        $services = $business->services()->get(['id', 'name', 'color', 'duration']);
        $contacts = $business->contacts()->get(['contacts.id', 'firstname', 'lastname']);
        $humanresources = $business->humanresources()->get(['id', 'name', 'capacity']);

        JavaScript::put([
            'minTime'      => $business->pref('start_at'),
            'maxTime'      => $business->pref('finish_at'),
            'events'       => $jsAppointments,
            'lang'         => $this->getActiveLanguage($business->locale),
            'slotDuration' => $slotDuration,
        ]);

        return view('manager.businesses.appointments.calendar', compact(
            'business', 'icalURL', 'services', 'contacts', 'humanresources'
        ));
    }

    public function postReschedule(Request $request, Business $business, Appointment $appointment)
    {
        $this->authorize('manage', $business);

        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after:start',
        ]);

        Log::info('agenda.reschedule', [
            'actor'     => auth()->id(),
            'resource'  => 'appointment',
            'operation' => 'reschedule_drag_drop',
            'context'   => [
                'business_id'    => $business->id,
                'appointment_id' => $appointment->id,
                'new_start'      => $request->input('start'),
                'new_end'        => $request->input('end'),
            ],
        ]);

        $appointment->start_at  = \Carbon\Carbon::parse($request->input('start'));
        $appointment->finish_at = \Carbon\Carbon::parse($request->input('end'));
        $appointment->save();

        return response()->json(['success' => true, 'message' => 'Appointment rescheduled.']);
    }

    public function getSharing(Business $business)
    {
        $this->authorize('manage', $business);

        Log::info('agenda.sharing.view', [
            'actor'     => auth()->id(),
            'resource'  => 'ical_feed',
            'operation' => 'view_sharing_screen',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->writeAuditRow($business, 'view_sharing_screen');

        $viewModel = $this->buildSharingViewModel($business);

        return view('manager.businesses.appointments.sharing', array_merge(
            compact('business'),
            $viewModel
        ));
    }

    public function postRotateToken(Request $request, Business $business)
    {
        $this->authorize('manage', $business);

        $newPlainToken = $this->tokenService->rotate($business);

        Log::info('agenda.sharing.rotate', [
            'actor'     => auth()->id(),
            'resource'  => 'ical_token',
            'operation' => 'rotate_token',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->writeAuditRow($business, 'rotate_token');

        $newUrl = route('business.ical.download', [$business, $newPlainToken]);

        if ($request->expectsJson()) {
            return response()->json([
                'url' => $newUrl,
                'message' => 'Token rotated successfully. The previous URL will no longer work.',
            ]);
        }

        return redirect()
            ->route('manager.business.agenda.sharing', [$business])
            ->with('new_token_url', $newUrl)
            ->with('flash_success', 'Token rotated. Copy your new URL below — it will not be shown again.');
    }

    private function buildSharingViewModel(Business $business): array
    {
        $activeToken = $this->tokenService->getActiveToken($business);
        $hasToken = $activeToken !== null;

        $maskedUrl = null;
        if ($hasToken) {
            $baseUrl = route('business.ical.download', [$business, 'TOKEN']);
            $maskedUrl = str_replace('TOKEN', '••••••••••••••••', $baseUrl);
        }

        $tokenMetadata = null;
        if ($hasToken) {
            $tokenMetadata = [
                'issued_at'   => $activeToken->created_at,
                'rotated_at'  => $activeToken->rotated_at,
                'last_used'   => $activeToken->last_used_at ?? null,
                'storage'     => 'SHA-256 (hashed)',
            ];
        }

        $guardMode = config('ical.guard_mode', 'shadow');
        $divergenceCount = $this->getDivergenceCount($business);

        $authorizationMatrix = [
            ['principal' => 'Owner', 'valid_token' => '200', 'invalid_token' => '403', 'no_token' => '404', 'revoked_token' => '403'],
            ['principal' => 'Non-owner', 'valid_token' => '200', 'invalid_token' => '403', 'no_token' => '404', 'revoked_token' => '403'],
            ['principal' => 'Anonymous', 'valid_token' => '200', 'invalid_token' => '403', 'no_token' => '404', 'revoked_token' => '403'],
        ];

        $denialLog = $this->getDenialLog($business);

        return compact(
            'hasToken',
            'maskedUrl',
            'tokenMetadata',
            'guardMode',
            'divergenceCount',
            'authorizationMatrix',
            'denialLog'
        );
    }

    private function getDivergenceCount(Business $business): int
    {
        return DB::table('audit_logs')
            ->where('entity_type', 'ical_feed')
            ->where('entity_id', (string) $business->id)
            ->where('action', 'guard_divergence')
            ->count();
    }

    private function getDenialLog(Business $business, int $limit = 50): array
    {
        return DB::table('audit_logs')
            ->where('entity_type', 'ical_feed')
            ->where('entity_id', (string) $business->id)
            ->where('action', 'access_denied')
            ->select(['created_at', 'action', 'correlation_id'])
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(context, '$.reason')) as reason")
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(context, '$.outcome')) as outcome")
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'timestamp'      => $row->created_at,
                'outcome'        => $row->outcome ?? 'denied',
                'reason'         => $row->reason ?? 'unknown',
                'correlation_id' => $row->correlation_id,
            ])
            ->toArray();
    }

    private function writeAuditRow(Business $business, string $action): void
    {
        DB::table('audit_logs')->insert([
            'actor_id'       => auth()->id(),
            'entity_type'    => 'ical_feed',
            'entity_id'      => (string) $business->id,
            'action'         => $action,
            'correlation_id' => request()->header('X-Correlation-ID', \Illuminate\Support\Str::uuid()->toString()),
            'context'        => json_encode(['ip_hash' => hash('sha256', config('app.key') . request()->ip())]),
            'created_at'     => now(),
        ]);
    }

    protected function getActiveLanguage($locale)
    {
        return session()->get('language', substr($locale, 0, 2));
    }

    protected function generateICalURL(Business $business): string
    {
        $activeToken = $this->tokenService->getActiveToken($business);

        if ($activeToken !== null) {
            $legacyToken = (new BusinessToken($business))->generate();
            return route('business.ical.download', [$business, $legacyToken]);
        }

        $legacyToken = (new BusinessToken($business))->generate();
        return route('business.ical.download', [$business, $legacyToken]);
    }
}
