<?php

use App\Models\Command;
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
        Schema::create('punishes', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Command::class);
            $table->bigInteger('twitch_user_id');
            $table->string('type')->comment('ban if the user is banned, timeout if timeout');
            $table->integer('seconds');

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
        Schema::dropIfExists('punishes');
    }
};
