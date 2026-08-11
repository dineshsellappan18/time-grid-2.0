<?php

namespace App\Models;

use App\Traits\HasRoles;
use App\Traits\Preferenceable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $username
 * @property string $password
 * @property string $last_ip
 * @property Carbon\Carbon $last_login_at
 * @property \Illuminate\Support\Collection $businesses
 * @property \Illuminate\Support\Collection $contacts
 * @property \Illuminate\Support\Collection $appointments
 */
class User extends EloquentModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, HasRoles, Notifiable, Preferenceable;

    /** @var list<string> */
    protected $fillable = ['name', 'email', 'username', 'password'];

    /** @var list<string> */
    protected $hidden = ['password', 'remember_token', 'last_ip', 'last_login_at'];

    /** @var list<string> */
    protected $dates = ['last_login_at'];

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class)->withTimestamps();
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function appointments(): HasManyThrough
    {
        return $this->hasManyThrough(
            \Timegridio\Concierge\Models\Appointment::class,
            Contact::class
        );
    }

    public function isOwnerOf(int|Business $business): bool
    {
        return $this->businesses()->withTrashed()->get()->contains($business);
    }

    public function hasBusiness(): bool
    {
        return $this->businesses->count() > 0;
    }

    public function hasContacts(): bool
    {
        return $this->contacts->count() > 0;
    }

    public function setUsernameAttribute(string $username): void
    {
        $username = strtolower(trim($username));

        $this->attributes['username'] = $username === '' ? md5(time().uniqid()) : $username;
    }

    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = ucwords(strtolower($name));
    }

    public function getContactSubscribedTo(int $businessId): ?Contact
    {
        return $this->contacts->filter(fn ($contact) => $contact->isSubscribedTo($businessId))->first();
    }
}
