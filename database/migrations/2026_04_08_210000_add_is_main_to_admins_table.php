<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->boolean('is_main')->default(false)->after('city_id');
        });

        $firstPlatformId = DB::table('admins')->whereNull('city_id')->orderBy('id')->value('id');
        if ($firstPlatformId) {
            DB::table('admins')->where('id', $firstPlatformId)->update(['is_main' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->dropColumn('is_main');
        });
    }
};
