<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_user', function (Blueprint $table) {
            $table->increments('user_id');
            $table->tinyInteger('is_super_admin')->default(0);
            $table->integer('create_time');
            $table->integer('update_time');
            $table->string('wallet_address',42)->nullable()->unique()->comment('用户的钱包地址');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_user');
    }
}
