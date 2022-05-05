<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DiscordGuild;
use App\Models\Club;

use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\File;
// use App\Services\SettingService;

use App\Events\UpdateDiscordChannelEvent;

use App\Helpers\TinyclubContract;
use App\Exceptions\ProgramException;

class UpdateDiscordDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     * 命令名称（执行时需要用到）
     * @var string
     */
    protected $signature = 'discord:update_data';

    /**
     * The console command description.
     * 命令描述
     * @var string
     */
    protected $description = 'discord机器人给每一个club更新member等信息';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    
    /**
     * Execute the console command.
     * 处理业务逻辑
     * @return int
     */
    public function handle()
    {
        $clubs = Club::where('contract_address','!=','')->get();
        foreach($clubs as $club) {
            if ($club->discord_guild) {
                $this->updateClubData($club);
            }
        }
    }

    public function updateClubData(Club $club) {

        $mint_data = $club->mint_data;
        Log::debug('获得club对应的mint_data是:'.json_encode($mint_data));

        $data = [
            'floor_price'   =>  $mint_data['sale_price'],
            'nft_sold'      =>  $mint_data['total_minted'],
        ];
        
        event(new UpdateDiscordChannelEvent($club->discord_guild,$data));

        return;
    }
}