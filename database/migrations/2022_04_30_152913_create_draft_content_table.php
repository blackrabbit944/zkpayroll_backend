<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDraftContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_draft_content', function (Blueprint $table) {

            $table->increments('id');

            $table->mediumText('content')->nullable();

            $table->integer('user_id');

            $table->integer('create_time');
            $table->integer('update_time')->nullable();
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
        Schema::dropIfExists('b_draft_content');
    }
}
