<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIcalTokensTable extends Migration
{
    public function up(): void
    {
        Schema::create('ical_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_id');
            $table->string('token_hash', 64)->unique('ical_tokens_hash_unique');
            $table->timestamp('rotated_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index('business_id', 'ical_tokens_business_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ical_tokens');
    }
}
