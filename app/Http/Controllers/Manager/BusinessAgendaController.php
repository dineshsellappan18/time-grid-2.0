<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\TG\Business\Token as BusinessToken;
use App\TG\ICalTokenService;
use Illuminate\Support\Facades\Log;
use JavaScript;
use Timegridio\Concierge\Concierge;
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

        $appointments = $this->concierge->business($business)->getUnarchivedAppointments();

        $viewKey = count($appointments) == 0
            ? 'manager.businesses.appointments.empty'
            : "manager.businesses.appointments.{$business->strategy}.index";

        $user = auth()->user();

        return view($viewKey, compact('business', 'appointments', 'user'));
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

        $appointments = $this->concierge->business($business)->getActiveAppointments();

        $jsAppointments = [];

        foreach ($appointments as $appointment) {
            $jsAppointments[] = [
                'title' => $appointment->contact->firstname.' / '.$appointment->service->name,
                'color' => $appointment->service->color,
                'start' => $appointment->start_at->copy()->timezone($business->timezone)->toIso8601String(),
                'end'   => $appointment->finish_at->copy()->timezone($business->timezone)->toIso8601String(),
                ];
        }

        $slotDuration = count($appointments) > 5 ? '0:15' : '0:30';

        $icalURL = $this->generateICalURL($business);

        unset($appointments);

        JavaScript::put([
            'minTime'      => $business->pref('start_at'),
            'maxTime'      => $business->pref('finish_at'),
            'events'       => $jsAppointments,
            'lang'         => $this->getActiveLanguage($business->locale),
            'slotDuration' => $slotDuration,
        ]);

        return view('manager.businesses.appointments.calendar', compact('business', 'icalURL'));
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
