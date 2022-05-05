<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_item_history', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('chain')->default('eth')->comment('所属链');
            $table->string('contract_address', 42)->default('')->comment('合约地址');
            $table->integer('token_id')->default(0)->comment('token_id');;

            $table->integer('item_id')->default(0)->comment('所属的item对应的id');;

            $table->string('action_name', 32)->default('')->comment('行为类型可以是sale,transfer,mint');
            $table->string('from_address', 42)->default('')->comment('行为开始的用户');
            $table->string('to_address', 42)->default('')->comment('行为结束用户');
            $table->string('tx_hash', 256)->default('')->comment('行为对应的tx_hash');
            $table->integer('action_time')->nullable();

            $table->decimal('price',12,6)->nullable()->comment('行为对应的价格（如果他是一个购买的记录的话）');

            $table->integer('create_time');
            $table->integer('update_time');

            $table->index(['item_id'],'item_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_item_history');
    }
}
