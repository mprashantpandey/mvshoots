<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table): void {
            $table->foreignId('city_id')->nullable()->after('email')->constrained('cities')->nullOnDelete();
        });

        Schema::create('city_partner', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['partner_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_partner');

        Schema::table('partners', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('city_id');
        });
    }
};
