<?php

namespace App\TG\Business;

use Illuminate\Support\Collection;

class Dashboard
{
    protected array $counter = [];

    protected array $boxes = [];

    public function __construct(
        private readonly object $business,
        private readonly object $time,
    ) {
        $this->init();

        $this->loadCounters();
    }

    protected function init(): void
    {
        $this->boxes = [
            'appointments_active_today' => [
                'icon'  => 'check',
                'color' => 'green',
                'title' => 'manager.businesses.dashboard.panel.title_appointments_active',
                'link'  => route('manager.business.agenda.index', $this->business),
                ],
            'appointments_canceled_today' => [
                'icon'  => 'minus-circle',
                'color' => 'red',
                'title' => 'manager.businesses.dashboard.panel.title_appointments_canceled',
                'link'  => route('manager.business.agenda.index', $this->business),
                ],
            'appointments_active_tomorrow' => [
                'icon'  => 'hourglass-o',
                'color' => 'yellow',
                'title' => 'manager.businesses.dashboard.panel.title_appointments_active',
                'link'  => route('manager.business.agenda.index', $this->business),
                ],
            'contacts_subscribed' => [
                'icon'  => 'users',
                'color' => 'green',
                'title' => 'manager.businesses.dashboard.panel.title_contacts_subscribed',
                'link'  => route('manager.addressbook.index', $this->business),
                ],
            'contacts_registered' => [
                'icon'  => 'users',
                'color' => 'aqua',
                'title' => 'manager.businesses.dashboard.panel.title_contacts_registered',
                'link'  => route('manager.addressbook.index', $this->business),
                ],
            'appointments_total' => [
                'icon'  => 'table',
                'color' => 'aqua',
                'title' => 'manager.businesses.dashboard.panel.title_appointments_total',
                'link'  => route('manager.business.agenda.index', $this->business),
                ],
        ];
    }

    protected function loadCounters(): void
    {
        $this->counter['appointments_active_today'] = $this->business->bookings()->active()->ofDate($this->time->today())->count();
        $this->counter['appointments_canceled_today'] = $this->business->bookings()->canceled()->ofDate($this->time->today())->count();
        $this->counter['appointments_active_tomorrow'] = $this->business->bookings()->active()->ofDate($this->time->tomorrow())->count();
        $this->counter['appointments_total'] = $this->business->bookings()->count();
        $this->counter['contacts_registered'] = $this->business->contacts()->count();
        $this->counter['contacts_subscribed'] = $this->business->contacts()->whereNotNull('user_id')->count();
    }

    public function getBoxes(): Collection
    {
        $bag = new Collection();

        foreach ($this->boxes as $key => $boxParameters) {
            $boxParameters['number'] = $this->counter[$key];
            $bag->push($boxParameters);
        }

        return $bag;
    }
}
