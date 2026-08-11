<?php

namespace App\Jobs;

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

    public function __construct(
        protected Humanresource $humanresource,
    ) {
    }

    public function handle(): void
    {
        Log::info('FetchICalFile: syncing calendar', [
            'humanresource_id' => $this->humanresource->id,
            'slug'             => $this->humanresource->slug,
        ]);

        $icalsync = new ICalSyncService();
        $icalsync->humanresource($this->humanresource)->sync();
    }
}
