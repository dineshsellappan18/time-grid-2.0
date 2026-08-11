<?php

namespace App\Http\Controllers\Manager;

use App\Events\NewContactWasRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactFormRequest;
use App\TG\AuditLogger;
use App\TG\ContactExportAssembler;
use App\TG\ContactEraser;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

class AddressbookController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly ContactExportAssembler $exporter,
        private readonly ContactEraser $eraser,
    ) {
        parent::__construct();
    }
    public function index(Business $business)
    {
        Log::info('addressbook.index', [
            'actor'     => auth()->id(),
            'resource'  => 'contacts',
            'operation' => 'list',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->authorize('manageContacts', $business);

        $contacts = $business->addressbook()->listing(100);

        return view('manager.contacts.index', compact('business', 'contacts'));
    }

    public function create(Business $business)
    {
        Log::info('addressbook.create_form', [
            'actor'     => auth()->id(),
            'resource'  => 'contact',
            'operation' => 'create_form',
            'context'   => ['business_id' => $business->id],
        ]);

        if ($business->contacts()->count() > plan('limits.contacts', $business->plan)) {
            flash()->warning(trans('app.saas.plan_limit_reached'));

            return redirect()->back();
        }

        $this->authorize('manageContacts', $business);

        $contact = new Contact();

        return view('manager.contacts.create', compact('business', 'contact'));
    }

    public function store(Business $business, ContactFormRequest $request)
    {
        Log::info('addressbook.store', [
            'actor'     => auth()->id(),
            'resource'  => 'contact',
            'operation' => 'create',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->authorize('manageContacts', $business);

        $contact = $business->addressbook()->register($request->all());

        if (!$contact->wasRecentlyCreated) {
            flash()->warning(trans('manager.contacts.msg.store.warning_showing_existing_contact'));

            return redirect()->route('manager.addressbook.show', [$business, $contact]);
        }

        $this->audit->append(
            action: 'contact.create',
            resourceType: 'contact',
            resourceId: $contact->id,
            changes: ['business_id' => $business->id],
        );

        event(new NewContactWasRegistered($contact));

        flash()->success(trans('manager.contacts.msg.store.success'));

        return redirect()->route('manager.addressbook.show', [$business, $contact]);
    }

    public function show(Business $business, Contact $contact)
    {
        Log::info('addressbook.show', [
            'actor'     => auth()->id(),
            'resource'  => 'contact',
            'operation' => 'view',
            'context'   => ['business_id' => $business->id, 'contact_id' => $contact->id],
        ]);

        $this->authorize('manageContacts', $business);

        $contact = $business->addressbook()->find($contact);

        return view('manager.contacts.show', compact('business', 'contact'));
    }

    public function edit(Business $business, Contact $contact)
    {
        Log::info('addressbook.edit', [
            'actor'     => auth()->id(),
            'resource'  => 'contact',
            'operation' => 'edit_form',
            'context'   => ['business_id' => $business->id, 'contact_id' => $contact->id],
        ]);

        $this->authorize('manageContacts', $business);

        $contact = $business->addressbook()->find($contact);

        $notes = $contact->pivot->notes;

        return view('manager.contacts.edit', compact('business', 'contact', 'notes'));
    }

    public function update(Business $business, Contact $contact, ContactFormRequest $request)
    {
        Log::info('addressbook.update', [
            'actor'     => auth()->id(),
            'resource'  => 'contact',
            'operation' => 'update',
            'context'   => ['business_id' => $business->id, 'contact_id' => $contact->id],
        ]);

        $this->authorize('manageContacts', $business);

        $data = $request->only([
            'firstname',
            'lastname',
            'email',
            'nin',
            'gender',
            'birthdate',
            'mobile',
            'mobile_country',
            'postal_address',
        ]);

        $contact = $business->addressbook()->update($contact, $data, $request->get('notes'));

        $this->audit->append(
            action: 'contact.rectify',
            resourceType: 'contact',
            resourceId: $contact->id,
            changes: array_keys($data),
        );

        flash()->success(trans('manager.contacts.msg.update.success'));

        return redirect()->route('manager.addressbook.show', [$business, $contact]);
    }

    public function destroy(Business $business, Contact $contact)
    {
        Log::info('addressbook.destroy', [
            'actor'     => auth()->id(),
            'resource'  => 'contact',
            'operation' => 'delete',
            'context'   => ['business_id' => $business->id, 'contact_id' => $contact->id],
        ]);

        $this->authorize('manageContacts', $business);

        $contactId = $contact->id;
        $contact = $business->addressbook()->remove($contact);

        $this->audit->append(
            action: 'contact.delete',
            resourceType: 'contact',
            resourceId: $contactId,
            changes: ['business_id' => $business->id],
        );

        flash()->success(trans('manager.contacts.msg.destroy.success'));

        return redirect()->route('manager.addressbook.index', $business);
    }

    public function export(Business $business, Contact $contact)
    {
        $this->authorize('export', [$contact, $business]);

        $data = $this->exporter->assemble($contact, $business);

        $this->audit->append(
            action: 'contact.export',
            resourceType: 'contact',
            resourceId: $contact->id,
            changes: ['business_id' => $business->id],
        );

        $filename = "contact-{$contact->id}-export-" . now()->format('Ymd') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Content-Type', 'application/json');
    }

    public function erase(Business $business, Contact $contact)
    {
        $this->authorize('erase', [$contact, $business]);

        $result = $this->eraser->erase($contact, $business);

        $this->audit->append(
            action: 'contact.erase',
            resourceType: 'contact',
            resourceId: $contact->id,
            outcome: 'success',
            changes: [
                'erased_fields' => $result['erased_fields'],
                'fully_deleted' => $result['fully_deleted'],
                'limitations' => $result['limitations'],
                'business_id' => $business->id,
            ],
        );

        if (!empty($result['limitations'])) {
            flash()->warning(implode(' ', $result['limitations']));
        } else {
            flash()->success(trans('manager.contacts.msg.erase.success'));
        }

        return redirect()->route('manager.addressbook.index', $business);
    }
}
