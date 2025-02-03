<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('avatar')->nullable();
            $table->string('type');

            $table->bigInteger('twitch_id');
            $table->string('twitch_access_token')->nullable();
            $table->string('twitch_refresh_token')->nullable();

            $table->dateTime('twitch_access_token_expires_at')->nullable();

            $table->dateTime('disabled_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
