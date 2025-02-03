<?php

use App\Models\Command;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Command::query()->withTrashed()->where('auto_post_id', 0)->update(['auto_post_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
