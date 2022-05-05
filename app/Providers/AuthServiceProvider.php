<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        Gate::policy('App\Models\Collection', 'App\Policies\CollectionPolicy');
        Gate::policy('App\Models\Item', 'App\Policies\ItemPolicy');
        Gate::policy('App\Models\Order', 'App\Policies\OrderPolicy');
        Gate::policy('App\Models\Link', 'App\Policies\LinkPolicy');
        Gate::policy('App\Models\Club', 'App\Policies\ClubPolicy');
        Gate::policy('App\Models\ClubUser', 'App\Policies\ClubUserPolicy');
        Gate::policy('App\Models\DiscordLog', 'App\Policies\DiscordLogPolicy');
        Gate::policy('App\Models\Tx', 'App\Policies\TxPolicy');
        Gate::policy('App\Models\Post', 'App\Policies\PostPolicy');

        Gate::define('unsplash-list', 'App\Policies\UnsplashPolicy@viewAny');

        // $this->app['auth']->viaRequest('api', function ($request) {
        //     if ($request->input('api_token')) {
        //         return User::where('api_token', $request->input('api_token'))->first();
        //     }
        // });
    }
}
