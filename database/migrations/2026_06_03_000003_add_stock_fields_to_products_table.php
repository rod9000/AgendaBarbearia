<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->after('purchase_price');
            $table->integer('min_stock')->default(0)->after('quantity');
            $table->string('supplier', 100)->nullable()->after('min_stock');
            $table->decimal('sale_price', 10, 2)->nullable()->after('supplier');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'min_stock', 'supplier', 'sale_price']);
        });
    }
};
