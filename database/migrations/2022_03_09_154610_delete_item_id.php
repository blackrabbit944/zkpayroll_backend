<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteItemId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b_order', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
        Schema::table('b_item_history', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b_order', function (Blueprint $table) {
            $table->integer('item_id')->default(0)->comment('所属的item对应的id');;
        });
        Schema::table('b_item_history', function (Blueprint $table) {
            $table->integer('item_id')->default(0)->comment('所属的item对应的id');;
        });
    }
}
