<?php

namespace App\Jobs;

use App\Exceptions\SsrfPolicyException;
use App\TG\AuditLogger;
use App\TG\Availability\ICalSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Humanresource;

class FetchICalFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function __construct(
        protected Humanresource $humanresource,
    ) {
    }

    public function handle(AuditLogger $audit): void
    {
        Log::info('FetchICalFile: syncing calendar', [
            'humanresource_id' => $this->humanresource->id,
            'slug'             => $this->humanresource->slug,
        ]);

        try {
            $icalsync = new ICalSyncService();
            $icalsync->humanresource($this->humanresource)->sync();

            $audit->append(
                action: 'ical.fetch',
                resourceType: 'humanresource',
                resourceId: (string) $this->humanresource->id,
                outcome: 'allowed',
                changes: ['url_host' => parse_url($this->humanresource->calendar_link, PHP_URL_HOST)],
            );
        } catch (SsrfPolicyException $e) {
            Log::channel('security')->warning('FetchICalFile: SSRF policy rejection', [
                'humanresource_id' => $this->humanresource->id,
                'reason'           => $e->getReason(),
            ]);

            $audit->append(
                action: 'ical.fetch',
                resourceType: 'humanresource',
                resourceId: (string) $this->humanresource->id,
                outcome: 'denied',
                changes: ['reason' => $e->getReason()],
            );

            if (!$e->isRetryable()) {
                $this->fail($e);
                return;
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('FetchICalFile: job failed permanently', [
            'humanresource_id' => $this->humanresource->id ?? null,
            'exception'        => $exception->getMessage(),
            'reason'           => $exception instanceof SsrfPolicyException
                ? $exception->getReason()
                : 'unknown',
        ]);
    }
}
