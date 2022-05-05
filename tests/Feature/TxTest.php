<?php

use App\Models\User;
use App\Models\DiscordGuild;
use App\Models\Club;
use App\Models\DiscordUser;

use App\Models\Tx;
use App\Services\TxService;

use App\Events\CheckNftHolderEvent;
use App\Listeners\CheckNftHolderListener;

use App\Events\CreateDiscordLogEvent;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TxTest extends TestCase
{
    private function createGuild() {

        $club = Club::factory()->create([
            'contract_address'  =>  '0x5a0d4479aed030305a36a1fb516346d533e794fb',
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
    public function f_tx_create() {

        Event::fake([CreateDiscordLogEvent::class,CheckNftHolderEvent::class]);

        $this->createGuild();

        $txService = new TxService();

        ///写入TX部分的测试
        $data = [
            'contract_address'  =>  '0x5a0d4479aed030305a36a1fb516346d533e794fb',
            'tx_hash'           =>  '0x'.Str::random(64),
            'from_address'      =>  '0x0000000000000000000000000000000000000000',
            'to_address'        =>  '0x19f43E8B016a2d38B483aE9be67aF924740ab893',
            'tx_type'           =>  'mint',
            'tx_log_id'         =>  1,
            'action_time'       =>  time()
        ];

        ///1.发现一个TX的时候写入数据库
        $tx = $txService->create($data);

            ///断言发送discord信息测试
            Event::assertDispatched(CreateDiscordLogEvent::class);

            ///断言进入了地址检查子程序
            Event::assertDispatched(CheckNftHolderEvent::class);

        ///2.写入重复的TX
        $tx2 = $txService->create($data);

            ///断言不会被执行
            $this->assertEquals($tx->id,$tx2->id);

        
    }
    /** @test */
    public function f_tx_create_with_event() {
        $this->createGuild();

        $txService = new TxService();

        ///写入TX部分的测试
        $data = [
            'contract_address'  =>  '0x5a0d4479aed030305a36a1fb516346d533e794fb',
            'tx_hash'           =>  '0x'.Str::random(64),
            'from_address'      =>  '0x0000000000000000000000000000000000000000',
            'to_address'        =>  '0x19f43E8B016a2d38B483aE9be67aF924740ab893',
            'tx_type'           =>  'mint',
            'action_index'      =>  1,
            'action_time'       =>  time()
        ];

        ///1.发现一个TX的时候写入数据库
        $tx = $txService->create($data);

    }
}

