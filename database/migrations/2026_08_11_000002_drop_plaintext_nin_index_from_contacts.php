<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPlaintextNinIndexFromContacts extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_nin_index');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->text('nin')->nullable()->change();
            $table->text('mobile')->nullable()->change();
            $table->text('birthdate')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('nin')->nullable()->change();
            $table->char('mobile', 15)->nullable()->change();
            $table->date('birthdate')->nullable()->change();
            $table->index('nin', 'contacts_nin_index');
        });
    }
}
