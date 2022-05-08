<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_salary', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('chain')->default('eth')->comment('所属链');
            $table->integer('user_id')->default(0)->comment('所属的创建人的user_id');;

            $table->string('name', 64)->default('')->comment('salary的所属人名字比如jack');
            $table->decimal('amount',36,18)->default(0)->comment('金额');;

            $table->char('address',42)->default('')->comment('钱包地址');;
            $table->char('contract_address',42)->default('')->comment('合约地址,例如usdt,usdc等合约');;

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
        Schema::dropIfExists('b_salary');
    }
}
