<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'webhook_enabled')) {
                $table->boolean('webhook_enabled')->default(true)->after('evolution_instance_name');
            }
            if (!Schema::hasColumn('companies', 'bot_enabled')) {
                $table->boolean('bot_enabled')->default(true)->after('webhook_enabled');
            }
            if (!Schema::hasColumn('companies', 'welcome_message')) {
                $table->text('welcome_message')->nullable()->after('bot_enabled');
            }
            if (!Schema::hasColumn('companies', 'off_hours_message')) {
                $table->text('off_hours_message')->nullable()->after('welcome_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['webhook_enabled', 'bot_enabled', 'welcome_message', 'off_hours_message']);
        });
    }
};
