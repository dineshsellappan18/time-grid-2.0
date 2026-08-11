<?php

namespace App\Policies;

use App\TG\AuditLogger;
use Timegridio\Concierge\Models\Business;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusinessPolicy
{
    use HandlesAuthorization;

    private AuditLogger $audit;

    public function __construct()
    {
        $this->audit = app(AuditLogger::class);
    }

    public function update(User $user, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business);

        if (!$allowed) {
            $this->audit->denied('business.update', 'business', $business->id);
        }

        return $allowed;
    }

    public function destroy(User $user, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business);

        if (!$allowed) {
            $this->audit->denied('business.destroy', 'business', $business->id);
        }

        return $allowed;
    }

    public function managePreferences(User $user, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business);

        if (!$allowed) {
            $this->audit->denied('business.manage_preferences', 'business', $business->id);
        }

        return $allowed;
    }

    public function manage(User $user, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business);

        if (!$allowed) {
            $this->audit->denied('business.manage', 'business', $business->id);
        }

        return $allowed;
    }

    public function manageContacts(User $user, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business);

        if (!$allowed) {
            $this->audit->denied('business.manage_contacts', 'business', $business->id);
        }

        return $allowed;
    }

    public function manageHumanresources(User $user, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business);

        if (!$allowed) {
            $this->audit->denied('business.manage_hr', 'business', $business->id);
        }

        return $allowed;
    }

    public function manageServices(User $user, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business);

        if (!$allowed) {
            $this->audit->denied('business.manage_services', 'business', $business->id);
        }

        return $allowed;
    }

    public function manageVacancies(User $user, Business $business): bool
    {
        $allowed = $user->isOwnerOf($business);

        if (!$allowed) {
            $this->audit->denied('business.manage_vacancies', 'business', $business->id);
        }

        return $allowed;
    }
}
