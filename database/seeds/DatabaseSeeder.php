<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Model::unguard();

        $this->call(NotifynderCategoriesSeeder::class);
        $this->command->info('Seeded the Notifynder Categories!');

        $this->call(CategoriesSeeder::class);
        $this->command->info('Seeded the Param Categories!');

        $this->call(CountriesSeeder::class);
        $this->command->info('Seeded the Param Countries!');

        $this->call(RolesTableSeeder::class);
        $this->command->info('Seeded the Param Roles!');
    }
}
