<?php

namespace Illuminate\Foundation\Bus;

class PendingDispatch
{
    protected $job;

    protected ?string $afterResponse = null;

    public function __construct($job)
    {
        $this->job = $job;
    }

    public function onConnection($connection): self
    {
        $this->job->onConnection($connection);

        return $this;
    }

    public function onQueue($queue): self
    {
        $this->job->onQueue($queue);

        return $this;
    }

    public function delay($delay): self
    {
        $this->job->delay($delay);

        return $this;
    }

    public function afterResponse(): self
    {
        $this->afterResponse = true;

        return $this;
    }

    public function __destruct()
    {
        app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($this->job);
    }
}
