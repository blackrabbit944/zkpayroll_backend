<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_collection', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('chain')->default('eth')->comment('所属链');
            $table->string('eip_type')->default('erc721')->comment('所属合约规范');
       
            $table->string('name', 64)->default('')->comment('Collection名字');
            $table->string('symbol', 32)->default('');
            $table->string('contract_address', 42)->unique()->default('')->comment('合约地址');
            $table->integer('item_count')->default(0)->comment('NFT Collection含有的item的数量');;

            $table->integer('avatar_img_id')->default(0)->comment('Avatar头像');
            $table->integer('cover_img_id')->nullable()->comment('封面底图');
            $table->float('cover_position', 5, 4)->default(0.0000)->comment('封面底图的偏移量');

            $table->float('float_price', 8,4)->nullable();
            $table->float('float_price_opensea', 8,4)->nullable();

            $table->string('discord_link', 256)->nullable();
            $table->string('twitter_link', 256)->nullable();
            $table->string('website_link', 256)->nullable();
            $table->string('instagram_link', 256)->nullable();

            $table->boolean('is_verify')->default(false);

            $table->integer('create_time');
            $table->integer('update_time');
            $table->integer('delete_time')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_collection');
    }
}
