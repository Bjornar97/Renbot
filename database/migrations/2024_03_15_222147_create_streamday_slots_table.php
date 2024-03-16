<?php

use App\Models\Creator;
use App\Models\Streamday;
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
        Schema::create('streamday_slots', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Streamday::class);
            $table->foreignIdFor(Creator::class);
            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streamday_slots');
    }
};
