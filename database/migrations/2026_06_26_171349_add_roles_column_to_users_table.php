<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('role');
        });

        DB::table('users')->where('role', 'admin')->update(['roles' => json_encode(['admin'])]);
        DB::table('users')->where('role', 'attendant')->update(['roles' => json_encode(['barbeiro'])]);
        DB::table('users')->whereNull('roles')->update(['roles' => json_encode(['barbeiro'])]);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};
