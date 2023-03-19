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
        Schema::create('commands', function (Blueprint $table) {
            $table->id();

            $table->string("command"); // Is not unique because of soft deletes
            $table->text("response")->nullable();

            $table->string("usable_by");
            $table->boolean("enabled");
            $table->integer("cooldown")->default(0)->comment("Cooldown until the same user can use the command again.");
            $table->integer("global_cooldown")->default(0)->comment("Cooldown until any user can use the same command again.");

            $table->string("type")->comment("The type of command this is. Is it a normal command, or a punishable command, or a special?");

            $table->tinyInteger("severity")->nullable()->comment("How hard a chatter should be punished. Only applicable to punishable commands.");
            $table->text("punish_reason")->nullable();

            $table->string("action")->nullable()->comment("This is the action to run. Only applies to special commands");

            $table->softDeletes();
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
        Schema::dropIfExists('commands');
    }
};
