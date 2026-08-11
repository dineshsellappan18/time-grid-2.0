<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $key
 * @property mixed $value
 * @property string $type
 */
class Preference extends EloquentModel
{
    /** @var list<string> */
    protected $fillable = ['key', 'value', 'type'];

    public function preferenceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function __toString(): string
    {
        return $this->attributes['value'];
    }

    public static function getDefault(object $model, string $key): self
    {
        $class = get_class($model);
        $value = config("preferences.{$class}.{$key}.value");
        $type = config("preferences.{$class}.{$key}.type");

        return new self([
            'key'                 => $key,
            'value'               => $value,
            'type'                => $type,
            'preferenceable_type' => $class,
            'preferenceable_id'   => $model,
            ]);
    }

    public function question(): string
    {
        return trans("preferences.{$this->preferenceable_type}.question.{$this->key}");
    }

    public function help(): string
    {
        return trans("preferences.{$this->preferenceable_type}.help.{$this->key}");
    }

    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function value(): mixed
    {
        return match ($this->type) {
            'string' => (string) $this->value,
            'bool' => (bool) $this->value,
            'int' => (int) $this->value,
            'float' => (float) $this->value,
            default => $this->value,
        };
    }
}
