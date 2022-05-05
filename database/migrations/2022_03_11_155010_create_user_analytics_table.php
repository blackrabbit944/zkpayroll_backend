<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAnalyticsTable extends Migration
{

     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_user_analytics', function (Blueprint $table) {
           
            $table->increments('id');
           
            $table->char('wallet_address',42)->nullable()->comment('钱包地址');

            $table->boolean('has_order')->default(0)->comment('是否有过挂单记录');
            $table->boolean('has_buy_record')->default(0)->comment('是否有过购买记录');
            $table->boolean('has_sell_record')->default(0)->comment('是否有过卖出记录');

            $table->char('invite_address',42)->nullable()->comment('邀请人的地址');

            $table->integer('create_time');
            $table->integer('update_time');

            $table->index(['wallet_address'], 'wallet_address');
            $table->index(['invite_address'], 'invite_address');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_user_analytics');
    }
}
