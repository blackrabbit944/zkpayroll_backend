<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIndexForItemHistory extends Migration
{

     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b_item_history', function (Blueprint $table) {
            $table->index(['contract_address','token_id','action_time'],'contract_address_token_id_and_time');
            $table->index(['action_name','action_time'],'action_name_and_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b_item_history', function (Blueprint $table) {
            $table->dropIndex('contract_address_token_id_and_time');
            $table->dropIndex('action_name_and_time');
        });
    }
}
