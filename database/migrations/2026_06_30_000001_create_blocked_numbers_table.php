<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('phone', 20)->index();
            $table->string('name')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_numbers');
    }
};
