<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_sign', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('wallet_address',42)->nullable()->comment('签名的钱包地址');
            $table->integer('sign_unixtime')->default(0)->comment('最后一次验证成功的签名的时间戳');;

            $table->integer('create_time');
            $table->integer('update_time');

            $table->index(['wallet_address'],'wallet_address');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_sign');
    }
}
