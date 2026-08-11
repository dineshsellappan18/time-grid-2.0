<?php

namespace App\Traits;

use App\Models\Preference;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Preferenceable
{
    public function preferences(): MorphMany
    {
        return $this->morphMany(Preference::class, 'preferenceable');
    }

    public function pref(string $key, mixed $value = null, string $type = 'string'): mixed
    {
        if (isset($value)) {
            $value = $this->cast($value, $type);

            $this->preferences()->updateOrCreate(compact('key'), compact('value', 'type'));

            return $value;
        }

        $pref = $this->preferences()->forKey($key)->first();

        if ($pref !== null) {
            return $pref->value();
        }

        $default = Preference::getDefault($this, $key);

        return  $default->value();
    }

    private function cast(mixed $value, string $type): mixed
    {
        return match ($type) {
            'bool' => boolval($value),
            'int' => intval($value),
            'float' => floatval($value),
            'string' => (string) $value,
            default => $value,
        };
    }
}
