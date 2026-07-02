<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'razao_social')) {
                $table->string('razao_social')->nullable()->after('name');
            }
            if (!Schema::hasColumn('companies', 'endereco')) {
                $table->string('endereco')->nullable()->after('cnpj');
            }
            if (!Schema::hasColumn('companies', 'numero')) {
                $table->string('numero')->nullable()->after('endereco');
            }
            if (!Schema::hasColumn('companies', 'bairro')) {
                $table->string('bairro')->nullable()->after('numero');
            }
            if (!Schema::hasColumn('companies', 'cidade')) {
                $table->string('cidade')->nullable()->after('bairro');
            }
            if (!Schema::hasColumn('companies', 'cep')) {
                $table->string('cep')->nullable()->after('cidade');
            }
            if (!Schema::hasColumn('companies', 'uf')) {
                $table->char('uf', 2)->nullable()->after('cep');
            }
            if (!Schema::hasColumn('companies', 'complemento')) {
                $table->string('complemento')->nullable()->after('uf');
            }
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'razao_social',
                'endereco',
                'numero',
                'bairro',
                'cidade',
                'cep',
                'uf',
                'complemento',
            ]);
        });
    }
};
