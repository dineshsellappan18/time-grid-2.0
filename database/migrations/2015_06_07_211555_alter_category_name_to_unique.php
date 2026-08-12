<?php

use Illuminate\Database\Migrations\Migration;

class AlterCategoryNameToUnique extends Migration
{
    public function up()
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('
                DELETE FROM notification_categories
                WHERE id NOT IN (
                    SELECT MIN(id) FROM notification_categories GROUP BY name
                )
            ');
        } else {
            DB::statement('
                DELETE n1 FROM notification_categories n1
                INNER JOIN notification_categories n2
                WHERE n1.id > n2.id AND n1.name = n2.name
            ');
        }

        Schema::table('notification_categories', function ($table) {
            $table->unique('name');
        });
    }

    public function down()
    {
        Schema::table('notification_categories', function ($table) {
            $table->dropUnique('notification_categories_name_unique');
        });
    }
}
