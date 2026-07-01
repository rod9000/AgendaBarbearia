<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('razao_social')->nullable()->after('name');
            $table->string('endereco')->nullable()->after('whatsapp');
            $table->string('numero')->nullable()->after('endereco');
            $table->string('bairro')->nullable()->after('numero');
            $table->string('cidade')->nullable()->after('bairro');
            $table->string('cep', 10)->nullable()->after('cidade');
            $table->string('uf', 2)->nullable()->after('cep');
            $table->string('complemento')->nullable()->after('uf');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['razao_social', 'endereco', 'numero', 'bairro', 'cidade', 'cep', 'uf', 'complemento']);
        });
    }
};
