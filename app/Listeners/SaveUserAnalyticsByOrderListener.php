<?php

namespace App\Listeners;

use App\Events\CreateOrderEvent;
// use App\Models\Notification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Services\UserService;


// use Intervention\Image\Facades\Image;
// use App\Helpers\Image as ImageHelper;

class SaveUserAnalyticsByOrderListener implements ShouldQueue
{

    /**
     * 任务将被发送到的连接的名称
     *
     * @var string|null
     */
    // public $connection = 'redis';

    /**
     * 最大尝试次数
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 任务将被发送到的队列的名称
     *
     * @var string|null
     */
    public $queue = 'push_data';

    /**
     * 任务被处理的延迟时间（秒）
     *
     * @var int
     */
    public $delay = 0;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

 

    /**
     * Handle the event.
     *
     * @param  \App\Events\CreateItemEvent  $event
     * @return void
     */
    public function handle(CreateOrderEvent $event)
    {

        // if (app()->environment('testing')) {
        //     return true;
        // }
        
        $order = $event->getOrder();
        Log::info('触发添加用户统计（User_Analytics）已经创建过订单的事件'.json_encode($order->toArray()));

        $row = UserService::setAnalytics($order->from_address,'has_order',1);

        if ($row) {
            Log::info('触发添加用户统计（User_Analytics）已经创建过订单->成功');
        }else {
            Log::info('触发添加用户统计（User_Analytics）已经创建过订单->失败');
        }

    }

    /**
     * 处理任务的失败
     *
     * @param  \App\Events\OrderShipped  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(CreateOrderEvent $event, $exception)
    {

        $order = $event->getOrder();
        Log::info('[队列失败]出发了添加用户统计（User_Analytics）已经创建过订单的事件,order:'.json_encode($order->toArray()));

    }


}
