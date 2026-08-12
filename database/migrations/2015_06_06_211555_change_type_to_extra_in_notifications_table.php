<?php

use Illuminate\Database\Migrations\Migration;

class ChangeTypeToExtraInNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function ($table) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql' || $driver === 'sqlite') {
                DB::statement('ALTER TABLE notifications MODIFY COLUMN extra json');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE notifications ALTER COLUMN extra TYPE json USING extra::json');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function ($table) {

            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql' || $driver === 'sqlite') {
                DB::statement('ALTER TABLE notifications MODIFY COLUMN extra VARCHAR(255)');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE notifications ALTER COLUMN extra TYPE VARCHAR(255)');
            }
        });
    }
}
