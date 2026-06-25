<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('webhook_enabled')->default(true)->after('evolution_instance_name');
            $table->boolean('bot_enabled')->default(true)->after('webhook_enabled');
            $table->text('welcome_message')->nullable()->after('bot_enabled');
            $table->text('off_hours_message')->nullable()->after('welcome_message');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['webhook_enabled', 'bot_enabled', 'welcome_message', 'off_hours_message']);
        });
    }
};
