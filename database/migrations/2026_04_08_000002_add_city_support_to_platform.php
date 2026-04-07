<?php

use App\Models\City;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('city_id')->nullable()->after('city')->constrained('cities')->nullOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->foreignId('city_id')->nullable()->after('user_id')->constrained('cities')->nullOnDelete();
        });

        Schema::create('category_city', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['category_id', 'city_id']);
        });

        Schema::create('city_plan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['plan_id', 'city_id']);
        });

        $cityNames = DB::table('users')
            ->whereNotNull('city')
            ->pluck('city')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        foreach ($cityNames as $index => $cityName) {
            City::firstOrCreate(
                ['name' => $cityName],
                ['status' => 'active', 'sort_order' => $index + 1],
            );
        }

        DB::table('users')
            ->whereNotNull('city')
            ->orderBy('id')
            ->select(['id', 'city'])
            ->get()
            ->each(function ($user): void {
                $cityName = trim((string) $user->city);
                if ($cityName === '') {
                    return;
                }

                $cityId = City::query()->where('name', $cityName)->value('id');
                if ($cityId) {
                    DB::table('users')->where('id', $user->id)->update(['city_id' => $cityId]);
                }
            });

        DB::table('bookings')
            ->orderBy('id')
            ->select(['id', 'user_id'])
            ->get()
            ->each(function ($booking): void {
                $cityId = DB::table('users')->where('id', $booking->user_id)->value('city_id');
                if ($cityId) {
                    DB::table('bookings')->where('id', $booking->id)->update(['city_id' => $cityId]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_plan');
        Schema::dropIfExists('category_city');

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('city_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('city_id');
        });
    }
};
