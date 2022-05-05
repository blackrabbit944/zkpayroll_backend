<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDiscordLogTableAddRetryTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b_discord_log', function (Blueprint $table) {
            $table->smallInteger('retry_times')->default(0)->comment('重试次数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b_discord_log', function (Blueprint $table) {
            $table->dropColumn('retry_times');
        });
    }
}
