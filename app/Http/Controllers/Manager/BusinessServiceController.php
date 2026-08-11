<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\TG\ServiceCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;

class BusinessServiceController extends Controller
{
    public function __construct(
        private readonly ServiceCatalogService $catalog,
    ) {
        parent::__construct();
    }

    public function index(Business $business)
    {
        Log::info('service.index', [
            'actor' => auth()->id(),
            'resource' => 'service',
            'operation' => 'list',
            'context' => ['business_id' => $business->id],
        ]);

        $this->authorize('manageServices', $business);

        return view('manager.businesses.services.index', compact('business'));
    }

    public function create(Business $business)
    {
        Log::info('service.create', [
            'actor' => auth()->id(),
            'resource' => 'service',
            'operation' => 'create_form',
            'context' => ['business_id' => $business->id],
        ]);

        if (!$this->catalog->canCreate($business)) {
            flash()->warning(trans('app.saas.plan_limit_reached'));

            return redirect()->back();
        }

        $this->authorize('manageServices', $business);

        $types = $business->servicetypes->pluck('name', 'id');

        $service = new Service([
            'duration' => $business->pref('service_default_duration'),
        ]);

        return view('manager.businesses.services.create', compact('business', 'service', 'types'));
    }

    public function store(Business $business, Request $request)
    {
        Log::info('service.store', [
            'actor' => auth()->id(),
            'resource' => 'service',
            'operation' => 'create',
            'context' => ['business_id' => $business->id],
        ]);

        $this->authorize('manageServices', $business);

        $service = $this->catalog->create(
            $business,
            $request->except('_token'),
            $request->get('type_id') ? (int) $request->get('type_id') : null,
        );

        flash()->success(trans('manager.service.msg.store.success'));

        return redirect()->route('manager.business.service.show', [$business, $service]);
    }

    public function show(Business $business, Service $service)
    {
        Log::info('service.show', [
            'actor' => auth()->id(),
            'resource' => 'service',
            'operation' => 'view',
            'context' => ['business_id' => $business->id, 'service_id' => $service->id],
        ]);

        $this->authorize('manageServices', $business);

        return view('manager.businesses.services.show', compact('service'));
    }

    public function edit(Business $business, Service $service)
    {
        Log::info('service.edit', [
            'actor' => auth()->id(),
            'resource' => 'service',
            'operation' => 'edit_form',
            'context' => ['business_id' => $business->id, 'service_id' => $service->id],
        ]);

        $this->authorize('manageServices', $business);

        $types = $business->servicetypes->pluck('name', 'id');

        return view('manager.businesses.services.edit', compact('service', 'types'));
    }

    public function update(Business $business, Service $service, Request $request)
    {
        Log::info('service.update', [
            'actor' => auth()->id(),
            'resource' => 'service',
            'operation' => 'update',
            'context' => ['business_id' => $business->id, 'service_id' => $service->id],
        ]);

        $this->authorize('manageServices', $business);

        $this->catalog->update(
            $service,
            $request->only(['name', 'color', 'duration', 'description', 'prerequisites']),
            $request->get('type_id') ? (int) $request->get('type_id') : null,
        );

        flash()->success(trans('manager.business.service.msg.update.success'));

        return redirect()->route('manager.business.service.show', [$business, $service]);
    }

    public function destroy(Business $business, Service $service)
    {
        Log::info('service.destroy', [
            'actor' => auth()->id(),
            'resource' => 'service',
            'operation' => 'delete',
            'context' => ['business_id' => $business->id, 'service_id' => $service->id],
        ]);

        $this->authorize('manageServices', $business);

        $this->catalog->destroy($service);

        flash()->success(trans('manager.services.msg.destroy.success'));

        return redirect()->route('manager.business.service.index', $business);
    }
}
