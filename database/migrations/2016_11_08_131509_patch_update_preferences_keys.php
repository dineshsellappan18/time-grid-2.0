<?php

use Illuminate\Database\Migrations\Migration;

class PatchUpdatePreferencesKeys extends Migration
{
    public function up(): void
    {
        // Data work moved to: php artisan preferences:remap-keys (WO-037)
        // This migration is intentionally a no-op; the schema is unchanged.
    }

    public function down(): void
    {
        // No schema change to reverse.
    }
}
