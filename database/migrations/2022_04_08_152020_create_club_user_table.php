<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ///创建CLUB和USER对应关系表

        Schema::create('b_club_user', function (Blueprint $table) {
     
            $table->increments('id');

            $table->char('contract_address',42)->comment('合约地址，用来区分不同club');
            $table->char('wallet_address',42)->comment('钱包地址');
            $table->integer('token_number')->comment('持有的Token数量');
            $table->string('status',16)->default('success')->comment('处理这个user的持有NFT的数据处理状态，只有success和failure两种状态，当failure时候代表这个记录需要重试');

            $table->integer('join_time')->default(0)->comment('最初进入的时间，如果一个用户进入了一个club，则代表了他最初进入这个club的时间');
            $table->integer('left_time')->default(0)->comment('最后离开时间，如果一个用户卖掉了自己持有的NFT，那么这个就标记着它被踢出时间');

            $table->integer('create_time');
            $table->integer('update_time')->nullable();
            $table->integer('delete_time')->nullable();

            // $table->index(['contract_address'],'contract_address');
            // $table->index(['wallet_address'],'wallet_address');
            $table->index(['contract_address','wallet_address'],'club_user');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_club_user');
    }
}
