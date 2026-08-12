<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropPlaintextNinIndexFromContacts extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $indexes = collect(DB::select('SHOW INDEX FROM contacts'))
                ->pluck('Key_name')
                ->unique();

            if ($indexes->contains('contacts_nin_index')) {
                Schema::table('contacts', function (Blueprint $table) {
                    $table->dropIndex('contacts_nin_index');
                });
            }

            DB::statement('ALTER TABLE contacts MODIFY nin TEXT NULL');
            DB::statement('ALTER TABLE contacts MODIFY mobile TEXT NULL');
            DB::statement('ALTER TABLE contacts MODIFY birthdate TEXT NULL');
        } else {
            // PostgreSQL: use standard ALTER COLUMN syntax
            $sm = DB::connection()->getDoctrineSchemaManager();
            $indexNames = collect($sm->listTableIndexes('contacts'))->keys();

            if ($indexNames->contains('contacts_nin_index')) {
                Schema::table('contacts', function (Blueprint $table) {
                    $table->dropIndex('contacts_nin_index');
                });
            }

            DB::statement('ALTER TABLE contacts ALTER COLUMN nin TYPE TEXT');
            DB::statement('ALTER TABLE contacts ALTER COLUMN mobile TYPE TEXT');
            DB::statement('ALTER TABLE contacts ALTER COLUMN birthdate TYPE TEXT');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE contacts MODIFY nin VARCHAR(255) NULL');
            DB::statement('ALTER TABLE contacts MODIFY mobile CHAR(15) NULL');
            DB::statement('ALTER TABLE contacts MODIFY birthdate DATE NULL');
        } else {
            DB::statement('ALTER TABLE contacts ALTER COLUMN nin TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE contacts ALTER COLUMN mobile TYPE CHAR(15)');
            DB::statement("ALTER TABLE contacts ALTER COLUMN birthdate TYPE DATE USING birthdate::date");
        }

        Schema::table('contacts', function (Blueprint $table) {
            $table->index('nin', 'contacts_nin_index');
        });
    }
}
