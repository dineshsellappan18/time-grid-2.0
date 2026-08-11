<?php

namespace App\TG;

use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;
use Illuminate\Support\Facades\Auth;

class SearchEngine
{
    protected array $results = [];

    protected array $scope = [];

    protected string $criteria;

    public function __construct(string $criteria)
    {
        $this->scope['businessesIds'] = auth()->user()->businesses->transform(
            fn ($item) => $item->id
        );

        $this->criteria = $criteria;
    }

    public function setBusinessScope(array $scope): static
    {
        $this->scope['businessesIds'] = $scope;

        return $this;
    }

    public function run(): static|false
    {
        if (strlen($this->criteria) < 3) {
            return false;
        }

        $this->getAppointments($this->criteria);
        $this->getContacts($this->criteria);
        $this->getServices($this->criteria);

        return $this;
    }

    public function results(): array
    {
        return $this->results;
    }

    private function getServices(string $expression): void
    {
        $this->results['services'] = Service::whereIn('business_id', $this->scope['businessesIds'])
            ->where('name', 'like', $expression.'%')->get();
    }

    private function getAppointments(string $expression): void
    {
        $this->results['appointments'] = Appointment::whereIn('business_id', $this->scope['businessesIds'])
            ->where('hash', 'like', $expression.'%')->get();
    }

    private function getContacts(string $expression): void
    {
        $businesses = Business::whereIn('id', $this->scope['businessesIds'])->get();
        foreach ($businesses as $business) {
            $collection = $business->contacts()->where(function ($query) use ($expression) {
                $query->where('lastname', 'like', $expression.'%')
                ->orWhere('firstname', 'like', $expression.'%')
                ->orWhere('nin', $expression)
                ->orWhere('mobile', 'like', '%'.$expression);
            })->get();

            $this->results['contacts'] = $collection;
        }
    }
}
