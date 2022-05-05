<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;

use App\Events\AdminNotificationEvent;
use App\Events\CreateItemEvent;
use App\Events\CreateItemHistoryEvent;
use App\Events\CreateOrderEvent;
use App\Events\CreateTxEvent;
use App\Events\BindDiscordGuildEvent;
use App\Events\UpdateDiscordChannelEvent;
use App\Events\CreateDiscordLogEvent;
use App\Events\CheckNftHolderEvent;


// use App\Events\SendDiscordMessageEvent;
// use App\Events\DiscordAddRoleEvent;
// use App\Events\DiscordRemoveRoleEvent;

use App\Listeners\SendDiscordNotificationListener;
use App\Listeners\QueryListener;
use App\Listeners\FetchNftImageListener;
use App\Listeners\BindDiscordGuildListener;
use App\Listeners\TestListener;

use App\Listeners\CheckNftHolderListener;
use App\Listeners\UpdateDiscordChannelListener;
use App\Listeners\CreateDiscordLogListener;

// use App\Listeners\SaveUserAnalyticsByOrderListener;
// use App\Listeners\SaveUserAnalyticsByItemHistoryListener;

use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        
        QueryExecuted::class => [
            QueryListener::class
        ],

        // AdminNotificationEvent::class => [
        //     SendDiscordNotificationListener::class
        // ],

        BindDiscordGuildEvent::class => [
            BindDiscordGuildListener::class
        ],

        // CheckNftHolderEvent::class => [
        //     CheckNftHolderListener::class
        // ],

        UpdateDiscordChannelEvent::class => [ ///更新discord的频道名字
            UpdateDiscordChannelListener::class
        ],

        CreateDiscordLogEvent::class => [
            CreateDiscordLogListener::class
        ],

        CheckNftHolderEvent::class => [
            CheckNftHolderListener::class
        ],

        // CreateItemEvent::class => [
        //     FetchNftImageListener::class
        // ],

        // CreateItemHistoryEvent::class => [
        //     SaveUserAnalyticsByItemHistoryListener::class
        // ],

        // CreateOrderEvent::class => [
        //     SaveUserAnalyticsByOrderListener::class
        // ],
    ];
}
