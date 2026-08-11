<?php

namespace App\TG\Availability;

use App\TG\ICalChecker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Timegridio\Concierge\Models\Humanresource;

class ICalSyncService
{
    protected ?Humanresource $humanresource = null;

    public function humanresource(Humanresource $humanresource): static
    {
        $this->humanresource = $humanresource;

        return $this;
    }

    public function sync(): bool
    {
        if (empty($this->humanresource->calendar_link)) {
            return false;
        }

        $icalFileContents = $this->getRemoteContents();

        Storage::put(
            $this->getFilePath("calendar-{$this->humanresource->slug}.ics"),
            $icalFileContents
        );

        $this->compile($this->humanresource->slug, $icalFileContents);

        return true;
    }

    public function compile(string $slug, string &$contents): void
    {
        $icalchecker = new ICalChecker();

        $icalchecker->loadString($contents);

        $events = collect($icalchecker->all());

        $events = $events->map(fn ($item) => "{$slug}:{$item->getStart()->toDateString()}")->unique()->sort();

        $compiled = implode("\n", $events->values()->all());

        $this->saveCompiled($compiled);
    }

    protected function saveCompiled(string $contents): bool
    {
        return Storage::append($this->getFilePath('ical-exclusion.compiled'), $contents);
    }

    public function getLocalContents(): string
    {
        $humanresourceSlug = $this->humanresource->slug;

        return Storage::get($this->getFilePath("calendar-{$humanresourceSlug}.ics"));
    }

    public function getRemoteContents(): string
    {
        return file_get_contents($this->humanresource->calendar_link);
    }

    protected function getFilePath(string $filename): string
    {
        $businessId = $this->humanresource->business->id;

        return 'business'.DIRECTORY_SEPARATOR.
                $businessId.DIRECTORY_SEPARATOR.
                'ical'.DIRECTORY_SEPARATOR.
                $filename;
    }
}
