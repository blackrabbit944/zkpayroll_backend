<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteDealOrderColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b_order', function (Blueprint $table) {
            $table->dropColumn('finish_time');
            $table->dropColumn('deal_price');
            $table->dropColumn('deal_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b_order', function (Blueprint $table) {
            $table->decimal('deal_price',12,6)->nullable()->comment('成交价格');
            $table->string('deal_address',42)->nullable()->comment('成交钱包地址');
            $table->integer('finish_time')->default(0)->comment('订单完成时间');

        });
    }
}
