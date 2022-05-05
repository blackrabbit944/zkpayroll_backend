<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_item', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('chain')->default('eth')->comment('所属链');
            $table->string('contract_address', 42)->default('')->comment('合约地址');
            $table->integer('collection_id')->default(0)->comment('所属的collection对应的id');;
           
            $table->integer('token_id')->default(0)->comment('token_id');;

            $table->string('image_url', 512)->default('')->comment('图片地址');
            $table->string('local_path', 512)->default('')->comment('本地抓的图片地址');

            $table->string('owner_address',42)->nullable()->comment('所有者钱包地址');

            $table->decimal('price',12,6)->nullable()->comment('处于on_sale状态的item的售价');
            $table->integer('expire_time')->nullable()->comment('处于on_sale状态的item的过期时间');

            $table->string('status',32)->nullable()->comment('item状态，可以是default,on_sale,on_auction');

            $table->integer('create_time');
            $table->integer('update_time');
            $table->integer('delete_time')->nullable();

            $table->index(['contract_address'],'contract_address');
            $table->unique(['contract_address','token_id'],'contract_address_token_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_item');
    }
}