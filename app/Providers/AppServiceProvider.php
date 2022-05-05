<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;


use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Log::debug('调用到AppServiceProvider的register');
    }

    public function boot()
    {
        Log::debug('调用到AppServiceProvider的boot');
        $this->bootEloquentMorphs();

        // $header = request()->header();
        // $true_ip = $header['cf-connecting-ip'][0] ?? '';
        // if (!$true_ip) {
        //     $true_ip = request()->ip(); 
        //     Log::info("cf-connecting-ip 是空的，将使用 request()->ip() 作为 IP 限制器");
        // }
        // app(\Illuminate\Cache\RateLimiter::class)->for('drawpixel', function () use ($true_ip) {
        //     return \Illuminate\Cache\RateLimiting\Limit::perHour(1000)->by($true_ip);
        // });
    }
    /**
     * 自定义多态关联的类型字段
     */
    private function bootEloquentMorphs()
    {
        Log::debug('调用到AppServiceProvider的bootEloquentMorphs');
        Relation::morphMap([
            'collection'        => \App\Models\Collection::class,
            'item'              => \App\Models\Item::class,
            'item_history'      => \App\Models\ItemHistory::class,
            'order'             => \App\Models\Order::class,
            'user'              => \App\Models\User::class,
            'upload_img'        => \App\Models\UploadImg::class,
        ]);
    }

}
