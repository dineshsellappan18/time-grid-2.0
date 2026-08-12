<?php

namespace Timegridio\Concierge\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Crypt;
use McCool\LaravelAutoPresenter\HasPresenter;
use Timegridio\Concierge\Presenters\ContactPresenter;
use Timegridio\Concierge\Traits\Presentable;

/**
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $nin
 * @property string $email
 * @property \Carbon\Carbon $birthdate
 * @property string $mobile
 * @property string $mobile_country
 * @property string $gender
 * @property string $occupation
 * @property string $martial_status
 * @property string $postal_address
 * @property string|null $nin_hash
 * @property string|null $mobile_hash
 * @property Illuminate\Support\Collection $businesses
 * @property Illuminate\Support\Collection $appointments
 * @property mixed $user
 * @property int $appointmentsCount
 */
class Contact extends EloquentModel implements HasPresenter
{
    use Presentable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'nin',
        'email',
        'birthdate',
        'mobile',
        'mobile_country',
        'gender',
        'occupation',
        'martial_status',
        'postal_address',
        ];

    /**
     * The attributes that should be hidden for arrays/JSON.
     *
     * @var array
     */
    protected $hidden = ['nin_hash', 'mobile_hash', 'pii_backfilled_at'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['birthdate'];

    public static function computeBlindIndex(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = preg_replace('/[^a-zA-Z0-9]/', '', $value);
        $normalized = strtolower($normalized);

        return hash_hmac('sha256', $normalized, config('app.key'));
    }

    //////////////////
    // Relationship //
    //////////////////

    /**
     * is profile of User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Relationship Contact belongs to User query
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * belongs to Business.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany Relationship Contact is part of Businesses addressbooks query
     */
    public function businesses()
    {
        return $this->belongsToMany(Business::class);
    }

    /**
     * has Appointments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relationship Contact has booked Appointments query
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /////////////////////
    // Soft Attributes //
    /////////////////////

    /**
     * has Appointment.
     *
     * @return bool Check if Contact has at least one Appointment booked
     */
    public function hasAppointment()
    {
        return $this->appointmentsCount > 0;
    }

    /**
     * Appointments Count.
     *
     * This method is used to optimize the relationship counting performance
     *
     * @return Illuminate\Database\Query Relationship Contact x Appointment count(*) query
     */
    public function appointmentsCount()
    {
        return $this
            ->hasMany(Appointment::class)
            ->selectRaw('contact_id, count(*) as aggregate')
            ->groupBy('contact_id');
    }

    /**
     * get AppointmentsCount.
     *
     * @return int Count of Appointments held by this Contact
     */
    public function getAppointmentsCountAttribute()
    {
        // If relation is not loaded already, let's do it first
        if (!array_key_exists('appointmentsCount', $this->relations)) {
            $this->load('appointmentsCount');
        }

        $related = $this->getRelation('appointmentsCount');

        // Return the count directly
        return ($related->count() > 0) ? (int) $related->first()->aggregate : 0;
    }

    ///////////////
    // Presenter //
    ///////////////

    /**
     * get presenter.
     *
     * @return ContactPresenter Presenter class
     */
    public function getPresenterClass()
    {
        return ContactPresenter::class;
    }

    //////////////
    // Mutators //
    //////////////

    /**
     * set Mobile — encrypts and updates blind index.
     */
    public function setMobileAttribute($mobile)
    {
        $plaintext = trim($mobile) ?: null;
        $this->attributes['mobile_hash'] = self::computeBlindIndex($plaintext);

        if ($plaintext === null) {
            $this->attributes['mobile'] = null;
            return null;
        }

        $this->attributes['mobile'] = Crypt::encryptString($plaintext);
        return $this->attributes['mobile'];
    }

    /**
     * set Mobile Country.
     *
     * @param string $country Country ISO Code ALPHA-2
     */
    public function setMobileCountryAttribute($country)
    {
        return $this->attributes['mobile_country'] = trim($country) ?: null;
    }

    /**
     * set Birthdate — encrypts.
     */
    public function setBirthdateAttribute(Carbon $birthdate = null)
    {
        if ($birthdate === null) {
            $this->attributes['birthdate'] = null;
            return null;
        }

        $this->attributes['birthdate'] = Crypt::encryptString($birthdate->toDateString());
        return $this->attributes['birthdate'];
    }

    /**
     * set Email.
     *
     * @param string $email Valid email address
     */
    public function setEmailAttribute($email)
    {
        return $this->attributes['email'] = empty(trim($email)) ? null : $email;
    }

    /**
     * set NIN — encrypts and updates blind index.
     */
    public function setNinAttribute($nin)
    {
        $plaintext = empty(trim($nin)) ? null : $nin;
        $this->attributes['nin_hash'] = self::computeBlindIndex($plaintext);

        if ($plaintext === null) {
            $this->attributes['nin'] = null;
            return null;
        }

        $this->attributes['nin'] = Crypt::encryptString($plaintext);
        return $this->attributes['nin'];
    }

    ///////////////
    // ACCESSORS //
    ///////////////

    public function getNinAttribute()
    {
        $value = $this->attributes['nin'] ?? null;
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return $value;
        }
    }

    public function getMobileAttribute()
    {
        $value = $this->attributes['mobile'] ?? null;
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return $value;
        }
    }

    public function getBirthdateAttribute()
    {
        $value = $this->attributes['birthdate'] ?? null;
        if ($value === null) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($value);
            return Carbon::parse($decrypted);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            try {
                return Carbon::parse($value);
            } catch (\Exception $e2) {
                return null;
            }
        }
    }

    public function getEmailAttribute()
    {
        if ($email = ($this->attributes['email'] ?? null)) {
            return $email;
        }

        if ($this->user) {
            return $this->user->email;
        }

        return null;
    }

    /////////////////////
    // Soft Attributes //
    /////////////////////

    /**
     * is Subscribed To Business.
     *
     * @param int $businessId Business of inquiry
     *
     * @return bool The Contact belongs to the inquired Business' addressbook
     */
    public function isSubscribedTo($businessId)
    {
        return $this->businesses->contains($businessId);
    }

    /**
     * is Profile of User.
     *
     * @param int $userId User of inquiry
     *
     * @return bool The Contact belongs to the inquired User
     */
    public function isProfileOf($userId)
    {
        return $this->user ? $this->user->id == $userId : false;
    }
}
