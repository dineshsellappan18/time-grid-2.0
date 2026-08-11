<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactPiiBlindIndexColumns extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('nin_hash', 64)->nullable()->after('nin');
            $table->string('mobile_hash', 64)->nullable()->after('mobile');
            $table->timestamp('pii_backfilled_at')->nullable()->after('updated_at');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->index('nin_hash', 'contacts_nin_hash_index');
            $table->index('mobile_hash', 'contacts_mobile_hash_index');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_nin_hash_index');
            $table->dropIndex('contacts_mobile_hash_index');
            $table->dropColumn(['nin_hash', 'mobile_hash', 'pii_backfilled_at']);
        });
    }
}
