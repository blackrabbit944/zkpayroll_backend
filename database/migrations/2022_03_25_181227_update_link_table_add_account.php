<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateLinkTableAddAccount extends Migration
{

     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b_link', function (Blueprint $table) {

            $table->string('account',64)->nullable()->comment('account的数据，某些平台可能就没有url只有account');
            $table->string('platform',32)->nullable()->comment('社交平台名字');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b_link', function (Blueprint $table) {

            $table->dropColumn('account');
            $table->dropColumn('platform');

        });
    }
}
