<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('bot_response_delay_minutes')->default(60)->after('off_hours_message');
            $table->boolean('bot_off_hours_enabled')->default(true)->after('bot_response_delay_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['bot_response_delay_minutes', 'bot_off_hours_enabled']);
        });
    }
};
