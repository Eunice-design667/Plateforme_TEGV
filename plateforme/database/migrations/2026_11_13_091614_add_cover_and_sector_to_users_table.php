<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sector')->nullable()->after('primary_profile')->index();
            $table->string('cover_style')->nullable()->after('photo_path');
            $table->string('cover_path')->nullable()->after('cover_style');
        });

        // Existing profiles: deduce the sector from the main trade when it is a suggested one.
        foreach (config('tegv.trade_sectors') as $trade => $sector) {
            DB::table('users')->whereNull('sector')->where('primary_profile', $trade)->update(['sector' => $sector]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['sector']);
            $table->dropColumn(['sector', 'cover_style', 'cover_path']);
        });
    }
};
