<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('default_appointment_view', 20)->default('dayGridMonth')->after('active');
        });

        DB::table('users')->where('role', 'admin')->update(['default_appointment_view' => 'dayGridMonth']);
        DB::table('users')->where('role', 'attendant')->update(['default_appointment_view' => 'timeGridWeek']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('default_appointment_view');
        });
    }
};
