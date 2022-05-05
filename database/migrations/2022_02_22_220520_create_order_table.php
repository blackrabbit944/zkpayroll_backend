<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_order', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('chain')->default('eth')->comment('所属链');
            $table->string('contract_address', 42)->default('')->comment('合约地址');           
            $table->integer('token_id')->default(0)->comment('token_id');;

            // $table->integer('collection_id')->default(0)->comment('所属的collection对应的id');;
            $table->integer('item_id')->default(0)->comment('所属的item对应的id');;
            $table->string('from_address',42)->nullable()->comment('所有者钱包地址');
            $table->decimal('price',12,6)->nullable()->comment('价格');
            $table->string('currency',4)->default('eth')->comment('货币');

            $table->decimal('deal_price',12,6)->nullable()->comment('成交价格');
            $table->string('deal_address',42)->nullable()->comment('成交钱包地址');
            $table->integer('finish_time')->default(0)->comment('订单完成时间');

            $table->integer('create_time');
            $table->integer('update_time');
            $table->integer('expire_time')->comment('过期时间');
            $table->integer('delete_time')->nullable();

            $table->index(['item_id'],'item_id');
            $table->index(['contract_address'],'contract_address');
            $table->index(['contract_address','token_id','finish_time'],'item_index');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_order');
    }
}
