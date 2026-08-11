<?php

namespace App\Http\Controllers\Manager;

use App\Exceptions\BusinessAlreadyRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessFormRequest;
use App\TG\AuditLogger;
use App\TG\Business\Dashboard;
use App\TG\BusinessService;
use Carbon\Carbon;
use Fenos\Notifynder\Facades\Notifynder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Category;

class BusinessController extends Controller
{
    protected ?array $location = null;

    public function __construct(
        private readonly BusinessService $businessService,
        private readonly Carbon $time,
        private readonly AuditLogger $audit,
    ) {
        parent::__construct();
    }

    public function index(): View|RedirectResponse
    {
        Log::info('business.index', [
            'actor'     => auth()->id(),
            'resource'  => 'businesses',
            'operation' => 'list',
        ]);

        $businesses = auth()->user()->businesses;

        if ($businesses->count() == 1) {
            flash()->success(trans('manager.businesses.msg.index.only_one_found'));

            return redirect()->route('manager.business.show', $businesses->first());
        }

        $user = auth()->user();

        return view('manager.businesses.index', compact('businesses', 'user'));
    }

    public function create(string $plan = 'free'): View
    {
        Log::info('business.create_form', [
            'actor'     => auth()->id(),
            'resource'  => 'business',
            'operation' => 'create_form',
            'context'   => ['plan' => $plan],
        ]);

        $timezone = $this->guessTimezone(null);

        $countryCode = $this->getCountry();

        $locale = app()->getLocale();

        $categories = $this->listCategories();

        $business = new Business();

        return view('manager.businesses.create', compact(
            'business',
            'timezone',
            'categories',
            'plan',
            'countryCode',
            'locale'
        ));
    }

    public function store(BusinessFormRequest $request): RedirectResponse
    {
        Log::info('business.store', [
            'actor'     => auth()->id(),
            'resource'  => 'business',
            'operation' => 'create',
        ]);

        try {
            $business = $this->businessService->register(auth()->user(), $request->all(), $request->get('category'));

            $this->businessService->setup($business);
        } catch (BusinessAlreadyRegistered $exception) {
            flash()->error(trans('manager.businesses.msg.store.business_already_exists'));

            return redirect()->back()->withInput(request()->all());
        }

        $this->audit->append(
            action: 'business.create',
            resourceType: 'business',
            resourceId: $business->id,
            changes: ['name' => $business->name],
        );

        $businessName = $business->name;
        Notifynder::category('user.registeredBusiness')
            ->from('App\Models\User', auth()->id())
            ->to('Timegridio\Concierge\Models\Business', $business->id)
            ->url('http://localhost')
            ->extra(compact('businessName'))
            ->send();

        flash()->success(trans('manager.businesses.msg.store.success'));

        return redirect()->route('manager.business.service.create', $business);
    }

    public function show(Business $business): View
    {
        Log::info('business.show', [
            'actor'     => auth()->id(),
            'resource'  => 'business',
            'operation' => 'view_dashboard',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->authorize('manage', $business);

        session()->set('selected.business', $business);

        $notifications = Notifynder::entity(Business::class)->getNotRead($business->id, 20);

        Notifynder::entity(Business::class)->readAll($business->id);

        $this->time->timezone($business->timezone);

        $dashboard = new Dashboard($business, $this->time);

        $boxes = $dashboard->getBoxes();

        $time = $this->time->toTimeString();

        return view('manager.businesses.show', compact('business', 'notifications', 'boxes', 'time'));
    }

    public function edit(Business $business): View
    {
        Log::info('business.edit', [
            'actor'     => auth()->id(),
            'resource'  => 'business',
            'operation' => 'edit_form',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->authorize('update', $business);

        $timezone = $this->guessTimezone($business->timezone);

        $categories = $this->listCategories();

        $category = $business->category_id;

        return view('manager.businesses.edit', compact('business', 'category', 'categories', 'timezone'));
    }

    public function update(Business $business, BusinessFormRequest $request): RedirectResponse
    {
        Log::info('business.update', [
            'actor'     => auth()->id(),
            'resource'  => 'business',
            'operation' => 'update',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->authorize('update', $business);

        $category = $request->get('category');

        $data = $request->only([
                'name',
                'description',
                'timezone',
                'postal_address',
                'phone',
                'social_facebook',
        ]);

        $this->businessService->update($business, $data);

        $this->businessService->setCategory($business, $category);

        $this->audit->append(
            action: 'business.update',
            resourceType: 'business',
            resourceId: $business->id,
            changes: array_keys($data),
        );

        flash()->success(trans('manager.businesses.msg.update.success'));

        return redirect()->route('manager.business.show', compact('business'));
    }

    public function destroy(Business $business): RedirectResponse
    {
        Log::info('business.destroy', [
            'actor'     => auth()->id(),
            'resource'  => 'business',
            'operation' => 'deactivate',
            'context'   => ['business_id' => $business->id],
        ]);

        $this->authorize('destroy', $business);

        $this->businessService->deactivate($business);

        $this->audit->append(
            action: 'business.deactivate',
            resourceType: 'business',
            resourceId: $business->id,
        );

        flash()->success(trans('manager.businesses.msg.destroy.success'));

        return redirect()->route('manager.business.index');
    }

    protected function listCategories(): Collection
    {
        return Category::pluck('slug', 'id')->transform(
            fn ($item) => trans("app.business.category.{$item}")
        );
    }

    protected function guessTimezone(?string $timezone = null): ?string
    {
        if (!empty($timezone)) {
            return $timezone;
        }

        $this->getLocation();

        $identifiers = timezone_identifiers_list();

        return in_array($this->location['timezone'], $identifiers) ? $this->location['timezone'] : $timezone;
    }

    protected function getCountry(): ?string
    {
        $this->getLocation();

        return Arr::get($this->location, 'isoCode', null);
    }

    protected function getLocation(): array
    {
        if ($this->location === null) {
            $this->location = app('geoip')->getLocation()->toArray();
        }

        return $this->location;
    }
}
