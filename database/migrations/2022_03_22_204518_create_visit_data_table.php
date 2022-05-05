<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_visit_data', function (Blueprint $table) {
     
            $table->increments('id');

            $table->char('ip',16)->default('')->comment('访问用户IP');
            $table->string('cookie_uuid',256)->default('')->comment('JS生成的唯一UUID');
            $table->string('region',32)->default('')->comment('用户来源国家');

            $table->string('visit_type',32)->default('')->comment('访问类型，比如是click_link，visit_page等');
            $table->integer('visit_id')->default(0)->comment('访问的具体内容，如果是页面，那么就是页面的用户ID，如果是LINK那么就是LINK的ID');
       
            $table->string('third_data', 16)->nullable()->comment('点击的是大按钮/小按钮(click_link时候记录)');

            $table->integer('create_time');
            $table->integer('update_time');

            $table->index(['visit_type','visit_id'],'content');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_visit_data');
    }
}
