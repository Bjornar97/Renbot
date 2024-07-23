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
        Schema::create('command_metadata', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Command::class);
            $table->string('type');
            $table->string('key');
            $table->text('value');

            $table->timestamps();

            $table->unique(['command_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('command_metadata');
    }
};
