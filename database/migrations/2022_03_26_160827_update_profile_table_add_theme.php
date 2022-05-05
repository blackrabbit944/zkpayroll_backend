<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateProfileTableAddTheme extends Migration
{

     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b_profile', function (Blueprint $table) {

            $table->string('theme',32)->nullable()->comment('页面主题');
            $table->string('unique_name',32)->nullable()->comment('唯一的短URL名字，只能是英文');

            $table->index(['unique_name'],'unique_name');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b_profile', function (Blueprint $table) {

            $table->dropColumn('theme');
            $table->dropColumn('unique_name');
        });
    }
}
