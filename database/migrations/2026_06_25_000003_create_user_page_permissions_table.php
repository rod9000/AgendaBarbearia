<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_page_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('page');
            $table->timestamps();

            $table->unique(['user_id', 'page']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_page_permissions');
    }
};
