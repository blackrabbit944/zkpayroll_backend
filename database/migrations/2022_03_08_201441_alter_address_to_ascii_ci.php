<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterAddressToAsciiCi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE b_order CHANGE contract_address contract_address CHAR(42) NOT NULL DEFAULT '' COLLATE ascii_general_ci");
        DB::statement("ALTER TABLE b_order CHANGE from_address from_address  CHAR(42) NOT NULL DEFAULT ''  COLLATE ascii_general_ci");
        DB::statement("ALTER TABLE b_order CHANGE deal_address deal_address  CHAR(42) NULL DEFAULT ''  COLLATE ascii_general_ci");
        DB::statement("ALTER TABLE b_item_history CHANGE contract_address contract_address  CHAR(42) NOT NULL DEFAULT ''  COLLATE ascii_general_ci");
        DB::statement("ALTER TABLE b_item_history CHANGE from_address from_address  CHAR(42) NOT NULL DEFAULT ''  COLLATE ascii_general_ci");
        DB::statement("ALTER TABLE b_item_history CHANGE to_address to_address  CHAR(42) NOT NULL DEFAULT ''  COLLATE ascii_general_ci");
        DB::statement("ALTER TABLE b_item CHANGE contract_address contract_address  CHAR(42) NOT NULL DEFAULT ''  COLLATE ascii_general_ci");
        DB::statement("ALTER TABLE b_item CHANGE owner_address owner_address CHAR(42) NOT NULL DEFAULT ''  COLLATE ascii_general_ci");
        DB::statement("ALTER TABLE b_collection CHANGE contract_address contract_address CHAR(42) NOT NULL DEFAULT ''  COLLATE ascii_general_ci");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
