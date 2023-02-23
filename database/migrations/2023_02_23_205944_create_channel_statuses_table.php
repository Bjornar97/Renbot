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
        Schema::create('channel_statuses', function (Blueprint $table) {
            $table->id();

            $table->string("channel");
            $table->dateTime("live_at")->nullable()->comment("When the channel went live. NULL if the channel is offline.");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_statuses');
    }
};
