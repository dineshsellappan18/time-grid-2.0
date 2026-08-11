<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\TG\VacancyAuthoringService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JavaScript;
use Timegridio\Concierge\Models\Business;

class BusinessVacancyController extends Controller
{
    public function __construct(
        private readonly VacancyAuthoringService $vacancyService,
    ) {
        parent::__construct();
    }

    public function create(Business $business)
    {
        Log::info('vacancy.create', [
            'actor' => auth()->id(),
            'resource' => 'vacancy',
            'operation' => 'create_form',
            'context' => ['business_id' => $business->id],
        ]);

        $this->authorize('manageVacancies', $business);

        JavaScript::put([
            'services'       => $business->services->pluck('slug')->all(),
            'humanresources' => $business->humanresources->pluck('slug')->all(),
            'lang'           => $this->getActiveLanguage($business->locale),
        ]);

        $daysQuantity = $business->pref('vacancy_edit_days_quantity', config('root.vacancy_edit_days'));

        $dates = $this->vacancyService->generateAvailability($business, 'today', $daysQuantity);

        if ($business->services->isEmpty()) {
            flash()->warning(trans('manager.vacancies.msg.edit.no_services'));
        }

        $advanced = $business->services->count() > 3 || $business->pref('vacancy_edit_advanced_mode');

        $template = $this->vacancyService->recallStatements($business->id);
        if ($advanced && empty($template)) {
            $template = $this->vacancyService->getTemplate($business);
        }

        $servicesList = $business->services()->pluck('name', 'slug');
        $humanresourcesList = $business->humanresources()->pluck('name', 'slug');
        $weekdaysList = [
            'mon' => trans('datetime.weekday.monday'),
            'tue' => trans('datetime.weekday.tuesday'),
            'wed' => trans('datetime.weekday.wednesday'),
            'thu' => trans('datetime.weekday.thursday'),
            'fri' => trans('datetime.weekday.friday'),
            'sat' => trans('datetime.weekday.saturday'),
            'sun' => trans('datetime.weekday.sunday'),
        ];

        $startAt = Carbon::parse('today ' . $business->pref('start_at') . ' ' . $business->timezone)->format('h:i A');
        $finishAt = Carbon::parse('today ' . $business->pref('finish_at') . ' ' . $business->timezone)->format('h:i A');

        $viewParams = compact('business', 'dates', 'advanced', 'template', 'servicesList', 'humanresourcesList', 'weekdaysList', 'startAt', 'finishAt');

        return view('manager.businesses.vacancies.edit', $viewParams);
    }

    public function store(Business $business, Request $request)
    {
        Log::info('vacancy.store', [
            'actor' => auth()->id(),
            'resource' => 'vacancy',
            'operation' => 'publish_simple',
            'context' => ['business_id' => $business->id],
        ]);

        $this->authorize('manageVacancies', $business);

        $publishedVacancies = $request->get('vacancy');

        $changed = $this->vacancyService->publishSimpleMode($business, $publishedVacancies);

        if (!$changed) {
            flash()->warning(trans('manager.vacancies.msg.store.nothing_changed'));

            return redirect()->back();
        }

        flash()->success(trans('manager.vacancies.msg.store.success'));

        return redirect()->route('manager.business.show', [$business]);
    }

    public function storeBatch(Business $business, Request $request)
    {
        Log::info('vacancy.store_batch', [
            'actor' => auth()->id(),
            'resource' => 'vacancy',
            'operation' => 'publish_batch',
            'context' => ['business_id' => $business->id],
        ]);

        $this->authorize('manageVacancies', $business);

        $statements = $request->input('vacancies');
        $unpublish = (bool) $request->input('unpublish');
        $remember = (bool) $request->input('remember');

        $updated = $this->vacancyService->publishBatchMode($business, $statements, $unpublish, $remember);

        if (!$updated) {
            flash()->warning(trans('manager.vacancies.msg.store.nothing_changed'));

            return redirect()->back();
        }

        flash()->success(trans('manager.vacancies.msg.store.success'));

        return redirect()->route('manager.business.show', [$business]);
    }

    public function show(Business $business)
    {
        Log::info('vacancy.show', [
            'actor' => auth()->id(),
            'resource' => 'vacancy',
            'operation' => 'view_timetable',
            'context' => ['business_id' => $business->id],
        ]);

        $this->authorize('manageVacancies', $business);

        $daysQuantity = $business->pref('vacancy_edit_days_quantity', config('root.vacancy_edit_days'));

        $timetable = $this->vacancyService->buildTimetable($business, $daysQuantity);

        if ($business->services->isEmpty()) {
            flash()->warning(trans('manager.vacancies.msg.edit.no_services'));
        }

        return view('manager.businesses.vacancies.show', compact('business', 'timetable'));
    }

    public function update(Business $business, Request $request)
    {
        Log::info('vacancy.update', [
            'actor' => auth()->id(),
            'resource' => 'vacancy',
            'operation' => 'update_service_vacancies',
            'context' => ['business_id' => $business->id],
        ]);

        $this->authorize('manageVacancies', $business);

        $serviceId = $request->input('serviceId');
        $weekdays = $request->input('weekdays');

        $this->vacancyService->updateServiceVacancies($business, $serviceId, $weekdays);

        return response()->json(['status' => 'OK']);
    }

    protected function getActiveLanguage(?string $locale): string
    {
        return session()->get('language', substr($locale ?? 'en', 0, 2));
    }
}
