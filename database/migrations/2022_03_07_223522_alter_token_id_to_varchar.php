<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterTokenIdToVarchar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement("ALTER TABLE b_item_history CHANGE COLUMN token_id token_id VARCHAR(80) DEFAULT '0' NOT NULL COLLATE ascii_general_ci");
        DB::statement("ALTER TABLE b_item CHANGE COLUMN token_id token_id  VARCHAR(80) NOT NULL DEFAULT '0' COLLATE ascii_general_ci");
        DB::statement("ALTER TABLE b_order CHANGE COLUMN token_id token_id VARCHAR(80) NOT NULL DEFAULT '0' COLLATE ascii_general_ci");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE b_item_history CHANGE COLUMN token_id token_id INT NOT NULL  DEFAULT 0");
        DB::statement("ALTER TABLE b_item CHANGE COLUMN token_id token_id INT NOT NULL DEFAULT 0");
        DB::statement("ALTER TABLE b_order CHANGE COLUMN token_id token_id INT NOT NULL DEFAULT 0");
    }
}
