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
        // Automatic WhatsApp thanks were removed (testers' feedback): the visitor list is exported instead.
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropIndex(['thanks_status']);
            $table->dropColumn(['thanks_status', 'thanks_channel', 'thanks_message', 'thanks_error', 'thanks_sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('thanks_status')->default('pending')->index();
            $table->string('thanks_channel')->nullable();
            $table->text('thanks_message')->nullable();
            $table->string('thanks_error')->nullable();
            $table->timestamp('thanks_sent_at')->nullable();
        });
    }
};
