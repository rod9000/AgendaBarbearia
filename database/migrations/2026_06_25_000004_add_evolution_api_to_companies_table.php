<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('evolution_api_url')->nullable()->after('whatsapp');
            $table->string('evolution_api_key')->nullable()->after('evolution_api_url');
            $table->string('evolution_instance_name')->nullable()->after('evolution_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['evolution_api_url', 'evolution_api_key', 'evolution_instance_name']);
        });
    }
};
