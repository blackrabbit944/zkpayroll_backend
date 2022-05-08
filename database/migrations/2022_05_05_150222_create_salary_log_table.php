<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_salary_log', function (Blueprint $table) {
            
            $table->increments('id');

            $table->string('chain')->default('eth')->comment('所属链');
            $table->char('tx_id',66)->default('')->comment('发送交易所属的tx的id');

            $table->string('data')->default('')->comment('交易发送的一个json数据');

            $table->integer('create_time');
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
        Schema::dropIfExists('b_salary_log');
    }
}
