<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_status_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->string('changed_by_type');
            $table->unsignedBigInteger('changed_by_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_status_logs');
    }
};
