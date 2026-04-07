<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_kyc', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('aadhar_number', 12);
            $table->string('aadhar_front_path');
            $table->string('aadhar_back_path');
            $table->string('pan_number', 10);
            $table->string('pan_image_path');
            $table->string('selfie_path');
            $table->string('status', 32)->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->unique('partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_kyc');
    }
};
