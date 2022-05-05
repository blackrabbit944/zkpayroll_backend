<?php

use App\Models\User;
use App\Models\DiscordGuild;
use App\Models\DiscordLog;
use App\Models\DiscordUser;
use App\Models\ClubUser;

use App\Events\CreateDiscordLogEvent;
use App\Listeners\CreateDiscordLogListener;

use Illuminate\Support\Facades\DB;

class DiscordEventTest extends TestCase
{

    private function createGuild() {
        $guild = DiscordGuild::factory()->create([
            'guild_id'          =>  '962967474750500935',
            'discord_user_id'   =>  '815214988385320972',
            'icon'              =>  '1300ff6ae364ecbf3c648280b8fd49a8',
            'name'              =>  'test1',
            'webhook_token'     =>  '_-dl8HTr1eSLy7RX0gfLLDAzSdshI2EbETOKGGWOmBsL3h1N86g8KoVZsjq-blroJUgd',
            'webhook_id'        =>  '963071592705974393',
            'admin_webhook_token'   =>  'ihPXUWPtOWN0fAYGGqpEXeBjZ9NhfZX8_pWpTvGW40B7bkHcR3tPHhlJu-Ll8uRjS8CK',
            'admin_webhook_id'  =>  '963071592429150288',
            'is_init'           =>  '1'
        ]);

        $discord_user = DiscordUser::factory()->create([
            'discord_user_id'   =>  '815214988385320972'
        ]);

        $club_user = ClubUser::factory()->create([
            'contract_address'  =>  $guild->club->contract_address,
            'wallet_address'    =>  $discord_user->user->wallet_address
        ]);

        return $guild;
    }
    
    /** @test */
    public function f_discord_event_send_message_test() {
        $guild = $this->createGuild();

        $log = [
            'guild_id'  =>  $guild->guild_id,
            'club_id'   =>  $guild->club_id,
            'action_type'   =>  'send_notify',
            'content'   =>  '测试发送消息',
        ];
        $discord_log = DiscordLog::create($log);

        $event = new CreateDiscordLogEvent($discord_log);
        $listener = new CreateDiscordLogListener();
        $result = $listener->handle($event);

        
        $this->seeInDatabase('b_discord_log',[
            'id'            =>  $discord_log->id,
            'status'        =>  'success',
        ]);
    }

    /** @test */
    public function f_discord_event_send_admin_message_test() {
        $guild = $this->createGuild();

        $log = [
            'guild_id'  =>  $guild->guild_id,
            'club_id'   =>  $guild->club_id,
            'action_type'   =>  'send_admin_notify',
            'content'   =>  '测试发送消息',
        ];
        $discord_log = DiscordLog::create($log);

        $event = new CreateDiscordLogEvent($discord_log);
        $listener = new CreateDiscordLogListener();
        $result = $listener->handle($event);

        
        $this->seeInDatabase('b_discord_log',[
            'id'            =>  $discord_log->id,
            'status'        =>  'success',
        ]);
    }

    /** @test */
    public function f_discord_event_add_member_test() {
        $guild = $this->createGuild();

        $log = [
            'guild_id'  =>  $guild->guild_id,
            'club_id'   =>  $guild->club_id,
            'discord_user_id'   =>  '815214988385320972',
            'action_type'   =>  'add_member',
        ];
        $discord_log = DiscordLog::create($log);

        $event = new CreateDiscordLogEvent($discord_log);
        $listener = new CreateDiscordLogListener();
        $result = $listener->handle($event);
        
        $this->seeInDatabase('b_discord_log',[
            'id'            =>  $discord_log->id,
            'status'        =>  'success',
        ]);
    }

    /** @test */
    public function f_discord_event_remove_member_test() {
        $guild = $this->createGuild();

        $log = [
            'guild_id'  =>  $guild->guild_id,
            'club_id'   =>  $guild->club_id,
            'discord_user_id'   =>  '815214988385320972',
            'action_type'   =>  'remove_member',
        ];
        $discord_log = DiscordLog::create($log);

        $event = new CreateDiscordLogEvent($discord_log);
        $listener = new CreateDiscordLogListener();
        $result = $listener->handle($event);
        
        $this->seeInDatabase('b_discord_log',[
            'id'            =>  $discord_log->id,
            'status'        =>  'success',
        ]);
    }

    /** @test */
    public function f_discord_event_create_test()
    {
        Event::fake([CreateDiscordLogEvent::class]);

        //发布notification的discord事件测试
        $guild = $this->createGuild();

        $log = [
            'guild_id'  =>  $guild->guild_id,
            'club_id'   =>  $guild->club_id,
            'action_type'   =>  'send_notify',
            'content'   =>  '这里是一个测试ADMIN通知',
        ];
        DiscordLog::create($log);

        Event::assertDispatched(CreateDiscordLogEvent::class);

    }

}

