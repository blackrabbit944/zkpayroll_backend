<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateLinkTable extends Migration
{

     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b_link', function (Blueprint $table) {

            $table->boolean('is_visible')->default(1)->comment('是否可见');
            $table->dropColumn('is_hidden');
            $table->index(['user_id','show_type','is_visible'],'user_type_visible');


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

            $table->boolean('is_hidden')->default(0)->comment('是否隐藏');
            $table->dropColumn('is_visible');
            
            $table->dropIndex('user_type_visible');
        });
    }
}
