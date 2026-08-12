<?php

namespace Illuminate\Foundation\Configuration;

class Middleware
{
    protected array $global = [];

    protected array $groups = [];

    protected array $aliases = [];

    protected array $prepend = [];

    protected array $append = [];

    public function use(array $middleware): static
    {
        $this->global = $middleware;

        return $this;
    }

    public function prepend(array|string $middleware): static
    {
        $this->prepend = array_merge($this->prepend, (array) $middleware);

        return $this;
    }

    public function append(array|string $middleware): static
    {
        $this->append = array_merge($this->append, (array) $middleware);

        return $this;
    }

    public function group(string $group, array $middleware): static
    {
        $this->groups[$group] = $middleware;

        return $this;
    }

    public function web(array $middleware): static
    {
        $this->groups['web'] = $middleware;

        return $this;
    }

    public function api(array $middleware): static
    {
        $this->groups['api'] = $middleware;

        return $this;
    }

    public function alias(array $aliases): static
    {
        $this->aliases = array_merge($this->aliases, $aliases);

        return $this;
    }

    public function getGlobal(): array
    {
        return array_merge($this->prepend, $this->global, $this->append);
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }
}
