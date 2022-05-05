<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscordLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_discord_log', function (Blueprint $table) {
     
            ///记录所有discord操作事件，包括发送信息，踢出用户，把某个用户设置为member等
            $table->increments('id');

            $table->string('action_type',32)->nullable()->comment('操作的类型，目前有add_member,remove_member,send_message,send_admin_message');

            $table->string('guild_id',24)->comment('对应的discord_guild_id');
            $table->integer('club_id')->default(0)->comment('所属的ClubId');
            $table->string('discord_user_id',24)->nullable()->comment('对应的discord_id,可选，在add_member,remove_member中会有');

            $table->text('content')->nullable()->comment('对应信息，如果是send_message则是发送内容，其他的暂时不记录数据');

            $table->string('status',24)->comment('状态，分为：init,success,failure，未来可以针对failure的数据进行重试');

            $table->integer('create_time');
            $table->integer('update_time')->nullable();
            $table->integer('success_time')->nullable();

            $table->index(['club_id'],'club_id');
            $table->index(['discord_user_id'],'discord_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_discord_log');
    }
}
