<?php

namespace Illuminate\Database\Eloquent\Factories;

use Faker\Generator as Faker;
use Illuminate\Support\Collection;

abstract class Factory
{
    protected $model;
    protected $count = 1;
    protected $states = [];
    protected $faker;

    public function __construct(?Faker $faker = null)
    {
        $this->faker = $faker ?? \Faker\Factory::create();
    }

    abstract public function definition(): array;

    public static function new(array $attributes = []): static
    {
        $instance = new static();
        if ($attributes) {
            $instance->states[] = $attributes;
        }
        return $instance;
    }

    public function count(int $count): static
    {
        $clone = clone $this;
        $clone->count = $count;
        return $clone;
    }

    public function state(array|callable $state): static
    {
        $clone = clone $this;
        $clone->states[] = is_callable($state) ? $state() : $state;
        return $clone;
    }

    public function make(array $attributes = [])
    {
        $results = $this->buildModels($attributes, persist: false);

        return $this->count === 1 ? $results->first() : $results;
    }

    public function create(array $attributes = [])
    {
        $results = $this->buildModels($attributes, persist: true);

        return $this->count === 1 ? $results->first() : $results;
    }

    protected function buildModels(array $attributes, bool $persist): Collection
    {
        $models = new Collection();

        for ($i = 0; $i < $this->count; $i++) {
            $definition = $this->definition();

            foreach ($this->states as $state) {
                $definition = array_merge($definition, $state);
            }

            $definition = array_merge($definition, $attributes);

            $modelClass = $this->model ?? $this->resolveModelClass();
            $model = new $modelClass($definition);

            if ($persist) {
                $model->save();
            }

            $models->push($model);
        }

        return $models;
    }

    protected function resolveModelClass(): string
    {
        if ($this->model) {
            return $this->model;
        }

        $factoryClass = get_class($this);
        $modelName = str_replace('Factory', '', class_basename($factoryClass));

        return "App\\Models\\{$modelName}";
    }
}
