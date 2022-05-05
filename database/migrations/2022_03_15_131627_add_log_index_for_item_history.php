<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddLogIndexForItemHistory extends Migration
{

     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE b_item_history ADD COLUMN log_index INT NOT NULL");
        DB::statement("ALTER TABLE b_item_history ADD UNIQUE INDEX tx_hash_log_index (tx_hash, log_index)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE b_item_history DROP INDEX tx_hash_log_index");
        DB::statement("ALTER TABLE b_item_history DROP COLUMN log_index");
    }
}
