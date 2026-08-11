<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanresourceRequest;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Humanresource;

class HumanresourceController extends Controller
{
    public function index(Business $business)
    {
        Log::info('HumanresourceController@index', [
            'actor' => auth()->id(),
            'business_id' => $business->id,
        ]);

        $this->authorize('manageHumanresources', $business);

        $humanresources = $business->humanresources;

        return view('manager.businesses.humanresources.index', compact('business', 'humanresources'));
    }

    public function create(Business $business)
    {
        Log::info('HumanresourceController@create', [
            'actor' => auth()->id(),
            'business_id' => $business->id,
        ]);

        if ($business->humanresources()->count() > plan('limits.specialists', $business->plan)) {
            flash()->warning(trans('app.saas.plan_limit_reached'));

            return redirect()->back();
        }

        $this->authorize('manageHumanresources', $business);

        $humanresource = new Humanresource();
        return view('manager.businesses.humanresources.create', compact('business', 'humanresource'));
    }

    public function store(Business $business, HumanresourceRequest $request)
    {
        Log::info('HumanresourceController@store', [
            'actor' => auth()->id(),
            'business_id' => $business->id,
        ]);

        $this->authorize('manageHumanresources', $business);

        $humanresource = new Humanresource($request->validated());

        $humanresource->business()->associate($business->id);

        $humanresource->save();

        flash()->success(trans('manager.humanresources.msg.store.success'));

        return redirect()->route('manager.business.humanresource.show', [$business, $humanresource]);
    }

    public function show(Business $business, Humanresource $humanresource)
    {
        Log::info('HumanresourceController@show', [
            'actor' => auth()->id(),
            'business_id' => $business->id,
            'humanresource_id' => $humanresource->id,
        ]);

        $this->authorize('manageHumanresources', $business);

        return view('manager.businesses.humanresources.show', compact('business', 'humanresource'));
    }

    public function edit(Business $business, Humanresource $humanresource)
    {
        Log::info('HumanresourceController@edit', [
            'actor' => auth()->id(),
            'business_id' => $business->id,
            'humanresource_id' => $humanresource->id,
        ]);

        $this->authorize('manageHumanresources', $business);

        return view('manager.businesses.humanresources.edit', compact('business', 'humanresource'));
    }

    public function update(Business $business, Humanresource $humanresource, HumanresourceRequest $request)
    {
        Log::info('HumanresourceController@update', [
            'actor' => auth()->id(),
            'business_id' => $business->id,
            'humanresource_id' => $humanresource->id,
        ]);

        $this->authorize('manageHumanresources', $business);

        $humanresource->fill($request->validated());
        $humanresource->save();

        flash()->success(trans('manager.humanresources.msg.update.success'));

        return redirect()->route('manager.business.humanresource.show', [$business, $humanresource]);
    }

    public function destroy(Business $business, Humanresource $humanresource)
    {
        Log::info('HumanresourceController@destroy', [
            'actor' => auth()->id(),
            'business_id' => $business->id,
            'humanresource_id' => $humanresource->id,
        ]);

        $this->authorize('manageHumanresources', $business);

        $humanresource->delete();

        flash()->success(trans('manager.humanresources.msg.destroy.success'));

        return redirect()->route('manager.business.humanresource.index', $business);
    }
}
