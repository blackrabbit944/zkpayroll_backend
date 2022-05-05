<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_link', function (Blueprint $table) {
     
            $table->increments('id');

            $table->string('sort_id')->default(0)->comment('排序ID');
            $table->string('url', 1023)->default('')->comment('链接');

            $table->string('link_type', 32)->default('web')->comment('链接类型，可选instagram等');
            $table->string('text', 32)->default('')->comment('链接文案，可以为空');

            $table->string('show_type', 16)->default('icon')->comment('显示方式，可选小图标/大按钮，icon/button/text类型显示');

            $table->integer('user_id')->default(0)->comment('创建用户');;
           
            $table->boolean('is_hidden')->default(0)->comment('是否隐藏');

            $table->integer('create_time');
            $table->integer('update_time');
            $table->integer('delete_time')->nullable();

            $table->index(['user_id','show_type','is_hidden'],'user_type_hidden');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_link');
    }
}
