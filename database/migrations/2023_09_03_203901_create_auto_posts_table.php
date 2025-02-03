<?php

use App\Models\Command;
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
        Schema::create('auto_posts', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->integer('interval')->nullable();
            $table->string('interval_type')->nullable()->comment('seconds/minutes/hours/days');
            $table->integer('min_posts_between')->nullable();
            $table->dateTime('last_post')->useCurrent();
            $table->foreignIdFor(Command::class, 'last_command_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_posts');
    }
};
