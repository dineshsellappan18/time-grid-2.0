<?php

namespace App\Http\Controllers\User;

use App\Events\NewAppointmentWasBooked;
use App\Events\NewSoftAppointmentWasBooked;
use App\Http\Controllers\Controller;
use App\TG\AuditLogger;
use Carbon\Carbon;
use Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JavaScript;
use Notifynder;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Exceptions\DuplicatedAppointmentException;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

class AgendaController extends Controller
{
    public function __construct(
        private readonly Concierge $concierge,
        private readonly AuditLogger $audit,
    ) {
        parent::__construct();
    }

    public function getIndex(): View
    {
        Log::info('agenda.list', [
            'actor'     => auth()->id(),
            'resource'  => 'appointments',
            'operation' => 'list',
        ]);

        $appointments = auth()->user()->appointments()->orderBy('start_at')->unarchived()->get();

        return view('user.appointments.index', compact('appointments'));
    }

    public function getAvailability(Business $business, Request $request): View|RedirectResponse
    {
        Log::info('agenda.availability', [
            'actor'     => auth()->id(),
            'resource'  => 'business',
            'operation' => 'check_availability',
            'context'   => ['business_id' => $business->id],
        ]);

        if (auth()->user()) {
            if ($behalofOfId = $request->input('behalfOfId')) {
                $this->authorize('manageContacts', $business);

                $contact = $business->contacts()->find($behalofOfId);
            } else {
                if (!$contact = auth()->user()->getContactSubscribedTo($business->id)) {
                    flash()->warning(trans('user.booking.msg.you_are_not_subscribed_to_business'));

                    return redirect()->route('user.businesses.home', compact('business'));
                }
            }

            Notifynder::category('user.checkingVacancies')
               ->from('App\Models\User', auth()->id())
               ->to('Timegridio\Concierge\Models\Business', $business->id)
               ->url('http://localhost')
               ->send();
        }

        $date = $request->input('date', 'today');
        $days = $request->input('days', $business->pref('availability_future_days'));

        $startFromDate = $this->sanitizeDate($date);

        if ($startFromDate->isPast()) {
            $startFromDate = $this->sanitizeDate('today');
        }

        $includeToday = $business->pref('appointment_take_today');

        if ($startFromDate->isToday() && !$includeToday) {
            $startFromDate = $this->sanitizeDate('tomorrow');
        }

        $availability = $this->concierge
                             ->business($business)
                             ->vacancies()
                             ->generateAvailability($startFromDate->toDateString(), $days);

        JavaScript::put([
            'language'  => $this->getActiveLanguage($business->locale),
            'startDate' => $startFromDate->toDateString(),
            'endDate'   => $startFromDate->copy()->addDays($days)->toDateString(),
        ]);

        return view(
            'user.appointments.'.$business->strategy.'.book',
            compact('business', 'availability', 'startFromDate', 'contact')
        );
    }

    public function postStore(Request $request): View|RedirectResponse
    {
        $business = Business::findOrFail($request->input('businessId'));

        Log::info('agenda.book', [
            'actor'     => auth()->id(),
            'resource'  => 'appointment',
            'operation' => 'create',
            'context'   => ['business_id' => $business->id],
        ]);

        $email = $request->input('email');
        $contactId = $request->input('contact_id');
        $isOwner = false;

        $issuer = auth()->user();

        if ($issuer) {
            $isOwner = $issuer->isOwnerOf($business->id);
            $contact = $this->findSubscrbedContact($issuer, $isOwner, $business, $contactId);
        } else {
            $contact = $this->getContact($business, $email);

            if (!$contact) {
                flash()->warning(trans('user.booking.msg.store.not-registered'));

                return redirect()->back();
            }

            auth()->once(compact('email'));
        }

        $serviceId = $request->input('service_id');
        $service = $business->services()->find($serviceId);

        $date = Carbon::parse($request->input('_date'))->toDateString();
        $time = Carbon::parse($request->input('_time'))->toTimeString();
        $timezone = $request->input('_timezone') ?: $business->timezone;

        $comments = $request->input('comments');
        $issuer = auth()->id();

        $reservation = compact('issuer', 'contact', 'service', 'date', 'time', 'timezone', 'comments');

        try {
            $appointment = $this->concierge->business($business)->takeReservation($reservation);
        } catch (DuplicatedAppointmentException $e) {
            $code = $this->concierge->appointment()->code;

            Log::notice('agenda.duplicate', [
                'actor'     => auth()->id(),
                'resource'  => 'appointment',
                'operation' => 'create',
                'context'   => ['business_id' => $business->id, 'code' => $code],
            ]);

            flash()->warning(trans('user.booking.msg.store.sorry_duplicated', compact('code')));

            if ($isOwner) {
                return redirect()->route('manager.business.agenda.index', compact('business'));
            }

            return redirect()->route('user.agenda');
        }

        if (false === $appointment) {
            flash()->warning(trans('user.booking.msg.store.error'));

            return redirect()->back();
        }

        $this->audit->append(
            action: 'appointment.create',
            resourceType: 'appointment',
            resourceId: $appointment->id,
            changes: ['business_id' => $business->id, 'service_id' => $service?->id, 'date' => $date],
        );

        flash()->success(trans('user.booking.msg.store.success', ['code' => $appointment->code]));

        if (!$issuer) {
            event(new NewSoftAppointmentWasBooked($appointment));

            return view('guest.appointment.show', compact('appointment'));
        }

        event(new NewAppointmentWasBooked(auth()->user(), $appointment));

        if ($isOwner) {
            return redirect()->route('manager.business.agenda.index', compact('business'));
        }

        return redirect()->route('user.agenda', '#'.$appointment->code);
    }

    protected function getContact(Business $business, ?string $email): ?Contact
    {
        if ($business->pref('allow_guest_registration')) {
            $contact = $business->addressbook()->register(compact('email'));
        } else {
            $contact = $business->addressbook()->getSubscribed($email);
        }

        return $contact;
    }

    public function getValidate(Request $request, Business $business): View|RedirectResponse
    {
        $code = $request->input('code');
        $email = $request->input('email');

        if (strlen($code) < 4) {
            flash()->error(trans('user.booking.msg.validate.error.bad-code'));

            return view('guest.appointment.invalid');
        }

        $appointment = $business->bookings()
                                ->with('contact')
                                ->where('hash', 'like', "{$code}%")
                                ->whereHas('Contact', function ($q) use ($email) {
                                    $q->where('email', $email);
                                })->first();

        if (!$appointment) {
            flash()->error(trans('user.booking.msg.validate.error.no-appointment-was-found'));

            return redirect()->to('/');
        }

        if ($appointment->status == Appointment::STATUS_CONFIRMED) {
            flash()->success(trans('user.booking.msg.validate.success.your-appointment-is-already-confirmed'));

            return view('guest.appointment.show', compact('appointment'));
        }

        $appointment->doConfirm();

        flash()->success(trans('user.booking.msg.validate.success.your-appointment-was-confirmed'));

        return view('guest.appointment.show', compact('appointment'));
    }

    protected function findSubscrbedContact($issuer, bool $isOwner, Business $business, ?int $contactId): ?Contact
    {
        if ($contactId && $isOwner) {
            return $business->contacts()->find($contactId);
        }

        return $issuer->getContactSubscribedTo($business->id);
    }

    protected function getActiveLanguage(?string $locale): string
    {
        return session()->get('language', substr($locale, 0, 2));
    }

    protected function sanitizeDate(string $dateString): Carbon
    {
        try {
            $date = Carbon::parse($dateString);
        } catch (\Exception) {
            Log::warning('agenda.invalid_date', [
                'actor'     => auth()->id(),
                'resource'  => 'date',
                'operation' => 'parse',
                'context'   => ['input' => $dateString],
            ]);
            $date = Carbon::now();
        }

        return $date;
    }
}
