<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property \Illuminate\Support\Collection $roles
 */
class Permission extends EloquentModel
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
