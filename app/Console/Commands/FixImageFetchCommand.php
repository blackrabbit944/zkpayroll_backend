<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Events\CreateItemEvent;

use App\Exceptions\ProgramException;

class FixImageFetchCommand extends Command
{
    /**
     * The name and signature of the console command.
     * 命令名称（执行时需要用到）
     * @var string
     */
    protected $signature = 'fix:image_fetch {id}';

    /**
     * The console command description.
     * 命令描述
     * @var string
     */
    protected $description = '修复图片数据';


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
        $id = $this->argument('id');
        //$RPC = 'https://mainnet.infura.io/v3/c8f0e4781675484ba6c556b8147eb3b1';
        $item = Item::find($id);

        if ($item) {
            event(new CreateItemEvent($item));
            dump('已经把id为'.$id.'的item加入队列中');
        }

    }
}