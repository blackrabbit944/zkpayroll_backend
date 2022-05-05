<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_club', function (Blueprint $table) {
     
            $table->increments('id');

            $table->string('name',64)->nullable()->comment('Club名字');
            $table->string('introduction',1024)->nullable()->comment('Club介绍');
            $table->string('unique_name',32)->nullable()->comment('唯一的短URL名字，只能是英文');
            $table->string('name_in_nft',32)->nullable()->comment('NFT卡片上的名字，只能是英文');
            $table->string('nft_bg',32)->nullable()->comment('Club的展示主题');
            $table->smallInteger('nft_font')->nullable()->comment('字体编号');

            $table->char('contract_address',42)->nullable()->comment('club对应的NFT合约地址');
            $table->char('unique_hash',64)->nullable()->comment('club对应的unit256随机数');

            $table->integer('user_id')->default(0)->comment('用户ID');
            $table->integer('avatar_img_id')->nullable()->comment('头像');

            $table->decimal('price',18,6)->nullable()->comment('会员卡的单价');
            $table->string('passcard_contract_address',42)->nullable()->comment('它对应的NFT合约');
            $table->string('passcard_type',32)->nullable()->comment('NFT的passcard类型，用于生成Passcard图片');
            $table->integer('passcard_max_count')->nullable()->comment('passcard最多发行多少张，这个数字不允许变更');

            $table->integer('passcard_count')->nullable()->comment('会员卡已发行了多少张');
            $table->integer('passcard_holding_user_count')->nullable()->comment('持有会员卡的用户数量');
            $table->decimal('royalty',6,4)->nullable()->comment('版税');

            $table->decimal('total_mint_income',12,6)->default(0)->comment('所有mint的金额总计');
            $table->decimal('total_royalty_income',24,18)->default(0)->comment('所有版税总计');

            $table->decimal('fee',4,2)->default(0.12)->comment('默认0.12手续费');   ///不能用%在comment中会引起一个奇怪的报错

            $table->integer('create_time')->default(0);
            $table->integer('update_time')->nullable();
            $table->integer('delete_time')->nullable();

            $table->unique('unique_name');

            $table->index(['unique_hash'],'unique_hash');
            $table->index(['contract_address'],'contract_address');
            $table->index(['user_id'],'user_id');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_club');
    }
}
