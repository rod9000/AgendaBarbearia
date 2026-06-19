<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('recurring_frequency', 10)->nullable()->after('notes');
            $table->date('recurring_until')->nullable()->after('recurring_frequency');
            $table->unsignedBigInteger('parent_id')->nullable()->after('recurring_until');
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['recurring_frequency', 'recurring_until', 'parent_id']);
        });
    }
};
