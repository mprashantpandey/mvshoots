<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table): void {
            $table->id();
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->string('device_token', 191);
            $table->string('platform')->default('unknown');
            $table->timestamps();
            $table->unique(['user_type', 'user_id', 'device_token'], 'device_tokens_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
