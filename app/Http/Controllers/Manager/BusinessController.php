<?php

namespace App\Http\Controllers\Manager;

use App\Exceptions\BusinessAlreadyRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessFormRequest;
use App\TG\Business\Dashboard;
use App\TG\BusinessService;
use Carbon\Carbon;
use Fenos\Notifynder\Facades\Notifynder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Category;

class BusinessController extends Controller
{
    protected ?array $location = null;

    public function __construct(
        private readonly BusinessService $businessService,
        private readonly Carbon $time,
    ) {
        parent::__construct();
    }

    public function index(): View|RedirectResponse
    {
        logger()->info(__METHOD__);

        $businesses = auth()->user()->businesses;

        if ($businesses->count() == 1) {
            logger()->info('Only one business to show');

            flash()->success(trans('manager.businesses.msg.index.only_one_found'));

            return redirect()->route('manager.business.show', $businesses->first());
        }

        $user = auth()->user();

        return view('manager.businesses.index', compact('businesses', 'user'));
    }

    public function create(string $plan = 'free'): View
    {
        logger()->info(__METHOD__);
        logger()->info("plan:$plan");

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
        logger()->info(__METHOD__);

        try {
            $business = $this->businessService->register(auth()->user(), $request->all(), $request->get('category'));

            $this->businessService->setup($business);
        } catch (BusinessAlreadyRegistered $exception) {
            flash()->error(trans('manager.businesses.msg.store.business_already_exists'));

            return redirect()->back()->withInput(request()->all());
        }

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
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

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
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('update', $business);

        $timezone = $this->guessTimezone($business->timezone);

        $categories = $this->listCategories();

        $category = $business->category_id;

        logger()->info(sprintf('businessId:%s timezone:%s category:%s', $business->id, $timezone, $category));

        return view('manager.businesses.edit', compact('business', 'category', 'categories', 'timezone'));
    }

    public function update(Business $business, BusinessFormRequest $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

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

        flash()->success(trans('manager.businesses.msg.update.success'));

        return redirect()->route('manager.business.show', compact('business'));
    }

    public function destroy(Business $business): RedirectResponse
    {
        logger()->info(__METHOD__);

        $this->authorize('destroy', $business);

        logger()->info(sprintf('Deactivating: businessId:%s', $business->id));

        $this->businessService->deactivate($business);

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

        logger()->info(sprintf('TIMEZONE FALLBACK="%s" GUESSED="%s"', $timezone, $this->location['timezone']));

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
            logger()->info('Getting location');

            $geoip = app('geoip');

            $this->location = $geoip->getLocation();

            logger()->info(serialize($this->location));
        }

        return $this->location;
    }
}
