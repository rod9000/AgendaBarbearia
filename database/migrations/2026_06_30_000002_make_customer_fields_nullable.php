<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE customers MODIFY cpf VARCHAR(14) NULL');
        DB::statement('ALTER TABLE customers MODIFY birth_date DATE NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE customers MODIFY cpf VARCHAR(14) NOT NULL');
        DB::statement('ALTER TABLE customers MODIFY birth_date DATE NOT NULL');
    }
};
