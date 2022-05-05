<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_tx', function (Blueprint $table) {
     
            $table->increments('id');

            $table->string('chain')->default('eth')->comment('所属链');
            $table->string('contract_address', 42)->default('')->comment('合约地址');
           
            $table->string('tx_type', 32)->default('')->comment('行为类型可以是sale,transfer,mint');

            $table->integer('token_id')->default(0)->comment('token_id');;

            $table->string('from_address', 42)->default('')->comment('行为开始的用户');
            $table->string('to_address', 42)->default('')->comment('行为结束用户');
            $table->char('tx_hash', 66)->default('')->comment('行为对应的tx_hash');
            $table->integer('tx_log_id')->default(0)->comment('tx对应的交易index，用于处理一个tx里面多个交易情况');
            $table->integer('action_time')->nullable();

            $table->decimal('price',12,6)->nullable()->comment('行为对应的价格（如果他是一个购买的记录的话）');
            $table->decimal('royalty',20,18)->nullable()->comment('CLUB所有者拿到的分账金额');

            $table->integer('create_time');
            $table->integer('update_time');

            $table->index(['contract_address'],'contract_address');
            $table->index(['tx_hash'],'tx_hash');

        });

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_tx');
    }
}
