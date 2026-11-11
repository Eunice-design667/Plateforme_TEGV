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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('origin_church')->nullable();
            $table->boolean('has_no_church')->default(false);
            $table->string('whatsapp');
            $table->date('visited_on')->index();
            $table->string('device_hash', 64)->nullable()->index();
            $table->string('thanks_status')->default('pending')->index();
            $table->string('thanks_channel')->nullable();
            $table->text('thanks_message')->nullable();
            $table->string('thanks_error')->nullable();
            $table->timestamp('thanks_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
