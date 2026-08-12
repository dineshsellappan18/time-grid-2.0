<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('actor_type', 50);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 100);
            $table->string('resource_type', 100);
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('outcome', 20)->default('success');
            $table->json('changes')->nullable();
            $table->string('correlation_id', 64)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['resource_type', 'resource_id'], 'audit_resource_idx');
            $table->index('occurred_at', 'audit_occurred_idx');
            $table->index('correlation_id', 'audit_correlation_idx');
            $table->index(['actor_type', 'actor_id'], 'audit_actor_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
}
