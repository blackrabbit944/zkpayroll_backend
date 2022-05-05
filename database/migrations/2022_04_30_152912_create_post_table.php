<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_post', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title',256)->default('');
            $table->integer('draft_content_id');
            $table->integer('user_id');
            $table->integer('create_time')->default(0);
            $table->integer('update_time')->nullable();
            $table->integer('delete_time')->nullable();
            $table->integer('up_count')->default(0);
            $table->integer('down_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->integer('club_id')->default(0);

            $table->string('view_target',16)->default('member')->comment('可以设置为member会员可见或all所有用户可见');

            $table->index(['club_id'], 'club_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_post');
    }

}
