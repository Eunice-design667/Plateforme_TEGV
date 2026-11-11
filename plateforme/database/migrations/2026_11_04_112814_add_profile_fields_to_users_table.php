<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('photo_path')->nullable();
            $table->string('primary_profile')->nullable();
            $table->json('secondary_profiles')->nullable();
            $table->unsignedTinyInteger('years_experience')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->unsignedTinyInteger('birth_day')->nullable();
            $table->unsignedTinyInteger('birth_month')->nullable();
            $table->json('church_roles')->nullable();
            $table->json('other_roles')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('is_tegv_christian')->default(false);
            $table->timestamp('profile_completed_at')->nullable();
            $table->string('leader_status')->default('none')->index();
            $table->foreignId('leader_validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('leader_validated_at')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->index(['birth_month', 'birth_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('leader_validated_by');
            $table->dropIndex(['birth_month', 'birth_day']);
            $table->dropIndex(['leader_status']);
            $table->dropColumn([
                'first_name', 'last_name', 'photo_path', 'primary_profile', 'secondary_profiles',
                'years_experience', 'company', 'job_title', 'birth_day', 'birth_month', 'church_roles',
                'other_roles', 'whatsapp', 'contact_email', 'is_tegv_christian', 'profile_completed_at',
                'leader_status', 'leader_validated_at', 'is_admin',
            ]);
        });
    }
};
