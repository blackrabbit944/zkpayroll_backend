<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DiscordGuild;
use App\Models\DiscordLog;

use App\Models\Club;

use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\File;
// use App\Services\SettingService;

use App\Events\UpdateDiscordChannelEvent;

use App\Helpers\TinyclubContract;
use App\Exceptions\ProgramException;

use App\Events\CreateDiscordLogEvent;

class RetryDiscordFailMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     * 命令名称（执行时需要用到）
     * @var string
     */
    protected $signature = 'discord:retry_messages';

    /**
     * The console command description.
     * 命令描述
     * @var string
     */
    protected $description = 'discord机器人重发发送失败的discord消息机制';

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
        $fail_logs = DiscordLog::where([
            'status'        =>  'failed',
            'action_type'   =>  'send_notify'
        ])->where('retry_times','<',5)->get();
        foreach($fail_logs as $log) {
            $this->retryLog($log);
        }

        $fail_logs = DiscordLog::where([
            'status'    =>  'failed',
            'action_type'   =>  'send_admin_notify'
        ])->where('retry_times','<',5)->get();
        foreach($fail_logs as $log) {
            $this->retryLog($log);
        }
    }

    public function retryLog(DiscordLog $log) {

        if ($log->retry_times >= 5) {
            Log::debug('因为重试次数已经大于5次，不再对这个进行重试，log_id:'.$log->id);
            return false;
        }

        $log->retry_times += 1;
        $log->save();

        Log::debug('准备发起log重试，log_id:'.$log->id);

        event(new CreateDiscordLogEvent($log));

        Log::debug('已经发起log重试:'.$log->id);
    }
}