<?php

namespace App\TG;

use App\Exceptions\BusinessAlreadyRegistered;
use App\Models\User;
use App\TG\Business\Setup\SetupStaff;
use Illuminate\Support\Str;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Category;

class BusinessService
{
    private string $setupStaffClass = SetupStaff::class;

    public function register(User $user, array $data, int $category): Business
    {
        $slug = Str::slug($data['name']);

        if ($business = self::getExisting($user, $slug)) {
            return $business;
        }

        $business = new Business($data);

        $category = Category::find($category);
        $business->strategy = $category->strategy;
        $business->category()->associate($category);

        $business->save();

        auth()->user()->businesses()->attach($business);

        return $business;
    }

    public function getExisting(User $user, string $slug): Business|false
    {
        $business = Business::withTrashed()->where(['slug' => $slug])->first();

        if ($business === null) {
            return false;
        }

        logger()->info("Found existing businessId:{$business->id}");

        if (!$user->isOwnerOf($business->id)) {
            logger()->info("Already taken businessId:{$business->id}");
            throw new BusinessAlreadyRegistered();
        }

        logger()->info("Restoring owned businessId:{$business->id}");

        $business->restore();

        return $business;
    }

    public function deactivate(Business $business): ?bool
    {
        return $business->delete();
    }

    public function update(Business $business, array $data): Business
    {
        $business->where(['id' => $business->id])->update($data);

        return $business;
    }

    public function setCategory(Business $business, int $category): Business
    {
        $category = Category::find($category);
        $business->category()->associate($category);
        $business->save();

        return $business;
    }

    public function setup(Business $business): void
    {
        $setupStaff = $this->setupStaffClass;

        $setupStaff::createStaffMember($business);
    }
}
