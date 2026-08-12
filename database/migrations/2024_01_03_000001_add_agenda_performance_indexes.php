<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgendaPerformanceIndexes extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['business_id', 'start_at', 'status'], 'idx_appointments_biz_start_status');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->index('email', 'idx_contacts_email');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_appointments_biz_start_status');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('idx_contacts_email');
        });
    }
}
