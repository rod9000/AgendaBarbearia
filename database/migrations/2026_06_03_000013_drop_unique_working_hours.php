<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tableName = 'working_hours';
        $indexName = 'working_hours_user_id_day_of_week_unique';

        // Drop foreign keys that reference this index first
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = '$tableName' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
            AND COLUMN_NAME IN ('user_id')
        ");

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE `$tableName` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Now drop the index
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropUnique(['user_id', 'day_of_week']);
        });
    }

    public function down()
    {
        Schema::table('working_hours', function (Blueprint $table) {
            $table->unique(['user_id', 'day_of_week']);
        });
    }
};
