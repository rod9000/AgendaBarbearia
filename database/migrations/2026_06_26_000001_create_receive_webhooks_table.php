<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receive_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('instance')->nullable();
            $table->string('event')->nullable();
            $table->string('sender_phone')->nullable()->index();
            $table->string('remote_jid')->nullable();
            $table->boolean('from_me')->default(false);
            $table->text('message_content')->nullable();
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receive_webhooks');
    }
};
