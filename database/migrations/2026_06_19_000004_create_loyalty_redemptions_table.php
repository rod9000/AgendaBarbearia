<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoyaltyRedemptionsTable extends Migration
{
    public function up()
    {
        Schema::create('loyalty_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('loyalty_reward_id')->constrained()->onDelete('cascade');
            $table->integer('points_spent');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loyalty_redemptions');
    }
}
