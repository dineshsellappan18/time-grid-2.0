<?php

namespace App\TG;

use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;

class ServiceCatalogService
{
    public function create(Business $business, array $attributes, ?int $typeId = null): Service
    {
        $service = Service::firstOrNew($attributes);
        $service->business()->associate($business->id);

        if ($typeId) {
            $service->type()->associate($typeId);
        }

        $service->save();

        Log::info('service_catalog.created', [
            'service_id' => $service->id,
            'business_id' => $business->id,
        ]);

        return $service;
    }

    public function update(Service $service, array $attributes, ?int $typeId = null): Service
    {
        $service->update($attributes);

        if ($typeId) {
            $service->type()->associate($typeId);
            $service->save();
        }

        Log::info('service_catalog.updated', [
            'service_id' => $service->id,
        ]);

        return $service;
    }

    public function destroy(Service $service): void
    {
        $serviceId = $service->id;
        $service->forceDelete();

        Log::info('service_catalog.destroyed', [
            'service_id' => $serviceId,
        ]);
    }

    public function canCreate(Business $business): bool
    {
        return $business->services()->count() <= plan('limits.services', $business->plan);
    }
}
