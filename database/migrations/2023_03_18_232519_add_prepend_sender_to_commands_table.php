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
        Schema::table('commands', function (Blueprint $table) {
            $table->boolean('prepend_sender')->default(false)->comment("Will prepend the sender's name to the command response");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commands', function (Blueprint $table) {
            $table->dropColumn('prepend_sender');
        });
    }
};
