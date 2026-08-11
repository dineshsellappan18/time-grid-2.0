<?php

namespace App\Http\Controllers\API;

use App\Events\AppointmentWasCanceled;
use App\Events\AppointmentWasConfirmed;
use App\Http\Controllers\Controller;
use App\Http\Requests\AlterAppointmentRequest;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;

class BookingController extends Controller
{
    private Concierge $concierge;

    public function __construct(Concierge $concierge)
    {
        $this->concierge = $concierge;

        parent::__construct();
    }

    public function postAction(AlterAppointmentRequest $request)
    {
        $issuer = auth()->user();
        $business = Business::findOrFail($request->input('business'));
        $appointment = Appointment::findOrFail($request->input('appointment'));
        $action = $request->input('action');
        $widgetType = $request->input('widget');

        $this->authorize($action, $appointment);

        Log::info('booking.action', [
            'actor'     => $issuer->id,
            'resource'  => 'appointment',
            'operation' => $action,
            'context'   => ['business_id' => $business->id, 'appointment_id' => $appointment->id],
        ]);

        $this->concierge->business($business);

        $appointmentManager = $this->concierge->booking()->appointment($appointment->hash);

        switch ($action) {
            case 'cancel':
                $appointment = $appointmentManager->cancel();
                event(new AppointmentWasCanceled($issuer, $appointment));
                break;
            case 'confirm':
                $appointment = $appointmentManager->confirm();
                event(new AppointmentWasConfirmed($issuer, $appointment));
                break;
            case 'serve':
                $appointment = $appointmentManager->serve();
                break;
            default:
                abort(400);
                break;
        }

        $contents = [
            'appointment' => $appointment->load('contact'),
            'user'        => auth()->user(),
        ];

        $viewKey = "widgets.appointment.{$widgetType}._body";
        $html = view($viewKey, $contents)->render();

        return response()->json(['code' => 'OK', 'html' => $html]);
    }
}
