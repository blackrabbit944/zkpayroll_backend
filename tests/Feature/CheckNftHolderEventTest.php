<?php

use App\Models\User;
use App\Models\DiscordGuild;
use App\Models\DiscordLog;
use App\Models\DiscordUser;

use App\Models\Club;
use App\Models\ClubUser;

use App\Events\CheckNftHolderEvent;
use App\Listeners\CheckNftHolderListener;

use App\Events\CreateDiscordLogEvent;

use Illuminate\Support\Facades\DB;

class CheckNftHolderEventTest extends TestCase
{

    private function createGuild() {

        $club = Club::factory()->create([
            'contract_address'  =>  '0xe0be388ab81c47b0f098d2030a1c9ef190691a8a',
        ]);

        $guild = DiscordGuild::factory()->create([
            'club_id'           =>  $club->id,
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
        return $guild;
    }
    

    /** @test */
    public function f_check_nft_holder_event_test() {

        Event::fake([CreateDiscordLogEvent::class]);

        $guild = $this->createGuild();

        $user = User::factory()->create([
            'wallet_address'    =>  '0x19f43E8B016a2d38B483aE9be67aF924740ab893',
        ]);
        $guild_user = DiscordUser::factory()->create([
            'user_id'   =>  $user->user_id,
            'discord_user_id'   =>  '815214988385320972',
            'name'      =>  'blackrabbit',
            'avatar'    =>  '368539b3f4682778854429bb952d4712'
        ]);

        $event = new CheckNftHolderEvent('0xe0be388ab81c47b0f098d2030a1c9ef190691a8a','0x19f43E8B016a2d38B483aE9be67aF924740ab893');
        $listener = new CheckNftHolderListener();
        $result = $listener->handle($event);

        ///断言这个用户被加入了ClubUser表，
        $club_user = ClubUser::where([
            'contract_address'  => '0xe0be388ab81c47b0f098d2030a1c9ef190691a8a',
            'wallet_address'    => '0x19f43E8B016a2d38B483aE9be67aF924740ab893',
        ])->first();
        
        $this->seeInDatabase('b_club_user',[
            'contract_address'  => '0xe0be388ab81c47b0f098d2030a1c9ef190691a8a',
            'wallet_address'    => '0x19f43E8B016a2d38B483aE9be67aF924740ab893',
            'token_number'      =>  1,
            'status'            =>  'success',
            'left_time'         =>  0,
        ]);
        ///因为现在改为在discord成功加入以后才会写这个时间所以这里就无法获得了
        // $this->assertGreaterThan(0,$club_user->join_time);


        Event::assertDispatched(CreateDiscordLogEvent::class);


        ///检查一个用户卖空时候，这个用户需要被移除
        $fake_account = '0x19f43E8B016a2d38B483aE9be67aF924740ab894';
        $user->wallet_address = $fake_account;
        $user->save();

        $club_user->wallet_address = $fake_account;
        $club_user->save();

        $event = new CheckNftHolderEvent('0xe0be388ab81c47b0f098d2030a1c9ef190691a8a',$fake_account);
        $listener = new CheckNftHolderListener();
        $result = $listener->handle($event);

        ///断言这个用户被加入了ClubUser表，
        $club_user2 = ClubUser::where([
            'contract_address'  =>  '0xe0be388ab81c47b0f098d2030a1c9ef190691a8a',
            'wallet_address'    => $fake_account,
        ])->first();
        
        $this->seeInDatabase('b_club_user',[
            'contract_address'  => '0xe0be388ab81c47b0f098d2030a1c9ef190691a8a',
            'wallet_address'    => $fake_account,
            'token_number'      =>  0,
            'status'            =>  'success',
        ]);
        ///因为现在改为在discord成功加入以后才会写这个时间所以这里就无法获得了
        // $this->assertGreaterThan(0,$club_user2->left_time);

        Event::assertDispatched(CreateDiscordLogEvent::class);

    }

}

