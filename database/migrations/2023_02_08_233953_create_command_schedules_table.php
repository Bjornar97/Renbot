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
        Schema::create('command_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Command::class);

            $table->text("response")->nullable();

            $table->dateTime("start");
            $table->dateTime("end");

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
        Schema::dropIfExists('command_schedules');
    }
};
