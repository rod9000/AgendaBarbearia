<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->integer('menu_number')->unsigned();
            $table->string('label');
            $table->enum('action', ['booking', 'services', 'working_hours', 'consult', 'cancel', 'location', 'custom'])->default('custom');
            $table->text('response_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'menu_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_menu_items');
    }
};
