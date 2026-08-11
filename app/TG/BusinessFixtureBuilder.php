<?php

namespace App\TG;

use App\Models\User;
use App\TG\Contracts\BusinessProvisionerInterface;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Category;

class BusinessFixtureBuilder
{
    private ?User $owner = null;
    private array $attributes = [];
    private ?int $categoryId = null;
    private bool $withSetup = true;

    public function __construct(
        private readonly BusinessProvisionerInterface $provisioner,
    ) {
    }

    public static function make(): self
    {
        return new self(app(BusinessProvisionerInterface::class));
    }

    public function withOwner(User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function withAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function withCategory(int $categoryId): self
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function withoutSetup(): self
    {
        $this->withSetup = false;
        return $this;
    }

    public function create(): Business
    {
        $owner = $this->owner ?? User::factory()->create();

        $attributes = array_merge([
            'name'        => 'Test Business ' . uniqid(),
            'description' => 'A test business',
            'timezone'    => 'UTC',
            'locale'      => 'en_US',
        ], $this->attributes);

        $categoryId = $this->categoryId ?? $this->getDefaultCategory();

        $business = $this->provisioner->provision($owner, $attributes, $categoryId);

        if ($this->withSetup) {
            $this->provisioner->setup($business);
        }

        return $business;
    }

    private function getDefaultCategory(): int
    {
        $category = Category::first();
        if ($category === null) {
            $category = Category::create([
                'name'     => 'default',
                'slug'     => 'default',
                'strategy' => 'dateslot',
            ]);
        }

        return $category->id;
    }
}
