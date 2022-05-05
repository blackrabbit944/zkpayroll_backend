<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserAnalyticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b_user_analytics', function (Blueprint $table) {

            $table->tinyInteger('invite_count')->default(0)->comment('总计邀请的人数');

            $table->decimal('sell_volume',12,6)->nullable()->comment('总计卖出NFT的金额');
            $table->decimal('buy_volume',12,6)->nullable()->comment('总计买入NFT的金额');

            $table->tinyInteger('is_wl')->default(0)->comment('是否达到了白名单标准');
            $table->tinyInteger('is_blackgold_wl')->default(0)->comment('是否达到了BlackGold白名单标准');

            $table->index(['is_wl'], 'is_wl');
            $table->index(['is_blackgold_wl'], 'is_blackgold_wl');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b_user_analytics', function (Blueprint $table) {
            $table->dropColumn('invite_count');

            $table->dropColumn('sell_volume');
            $table->dropColumn('buy_volume');

            $table->dropColumn('is_wl');
            $table->dropColumn('is_blackgold_wl');
        });
    }
}
