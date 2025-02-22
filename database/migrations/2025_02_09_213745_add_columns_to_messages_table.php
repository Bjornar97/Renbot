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
        Schema::table('messages', function (Blueprint $table) {
            $table->uuid('message_id')->nullable()->after('id')->index();
            $table->uuid('reply_to_message_id')->nullable()->after('message_id');
            $table->string('username')->nullable()->after('twitch_user_id');
            $table->string('display_name')->nullable()->after('username')->index();
            $table->string('user_color', 8)->nullable()->after('display_name');
            $table->json('fragments')->nullable()->after('message');
            $table->json('badges')->nullable()->after('fragments');
            $table->dateTime('irc_recieved_at')->nullable();
            $table->dateTime('webhook_recieved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('message_id');
            $table->dropColumn('reply_to_message_id');
            $table->dropColumn('username');
            $table->dropColumn('display_name');
            $table->dropColumn('user_color');
            $table->dropColumn('badges');
            $table->dropColumn('fragments');
            $table->dropColumn('irc_recieved_at');
            $table->dropColumn('webhook_recieved_at');
        });
    }
};
