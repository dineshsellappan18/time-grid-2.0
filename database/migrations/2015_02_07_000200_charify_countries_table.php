<?php

use Illuminate\Database\Migrations\Migration;

class CharifyCountriesTable extends Migration
{
    public function up()
    {
        $table = \Config::get('countries.table_name');
        $prefix = DB::getTablePrefix();
        $driver = Schema::getConnection()->getDriverName();

        Schema::table($table, function () use ($table, $prefix, $driver) {
            $columns = [
                'country_code'    => 'CHAR(3)',
                'iso_3166_2'      => 'CHAR(2)',
                'iso_3166_3'      => 'CHAR(3)',
                'region_code'     => 'CHAR(3)',
                'sub_region_code' => 'CHAR(3)',
            ];

            foreach ($columns as $col => $type) {
                if ($driver === 'pgsql') {
                    DB::statement("ALTER TABLE {$prefix}{$table} ALTER COLUMN {$col} TYPE {$type}");
                    DB::statement("ALTER TABLE {$prefix}{$table} ALTER COLUMN {$col} SET NOT NULL");
                    DB::statement("ALTER TABLE {$prefix}{$table} ALTER COLUMN {$col} SET DEFAULT ''");
                } else {
                    DB::statement("ALTER TABLE {$prefix}{$table} MODIFY {$col} {$type} NOT NULL DEFAULT ''");
                }
            }
        });
    }

    public function down()
    {
        $table = \Config::get('countries.table_name');
        $prefix = DB::getTablePrefix();
        $driver = Schema::getConnection()->getDriverName();

        Schema::table($table, function () use ($table, $prefix, $driver) {
            $columns = [
                'country_code'    => 'VARCHAR(3)',
                'iso_3166_2'      => 'VARCHAR(2)',
                'iso_3166_3'      => 'VARCHAR(3)',
                'region_code'     => 'VARCHAR(3)',
                'sub_region_code' => 'VARCHAR(3)',
            ];

            foreach ($columns as $col => $type) {
                if ($driver === 'pgsql') {
                    DB::statement("ALTER TABLE {$prefix}{$table} ALTER COLUMN {$col} TYPE {$type}");
                } else {
                    DB::statement("ALTER TABLE {$prefix}{$table} MODIFY {$col} {$type} NOT NULL DEFAULT ''");
                }
            }
        });
    }
}
