<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrderTabe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_id');
            $table->integer('discount_percentage')->default(0)->after('total_price');
            $table->integer('discount_value')->default(0)->after('discount_percentage');
            $table->integer('cash')->default(0)->after('discount_value');
            $table->integer('change')->default(0)->after('cash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->dropColumn('discount_percentage');
            $table->dropColumn('discount_value');
            $table->dropColumn('cash');
            $table->dropColumn('change');
        });
    }
}