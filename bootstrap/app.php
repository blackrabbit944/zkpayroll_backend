<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../app/Helpers/main.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));


if (env('APP_ENV') == 'production') {
    error_reporting(0);
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
$app->instance('path.lang', app()->basePath() . DIRECTORY_SEPARATOR . 'resources/lang');

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');
$app->configure('recaptcha');
$app->configure('jwt');
$app->configure('global');
$app->configure('discord');
$app->configure('image');
$app->configure('tinker');
$app->configure('unsplash');
$app->configure('queue');
$app->configure('telegram');
$app->configure('ethereum');
$app->configure('services');
$app->configure('scout');
$app->configure('reputation');
$app->configure('boardcasting');
$app->configure('grpc');
$app->configure('misc');
$app->configure('nft');
$app->configure('link');

/*定义alias别名*/

///这部分是邮件发送会用到的
// $app->alias('mail.manager', Illuminate\Mail\MailManager::class);
// $app->alias('mail.manager', Illuminate\Contracts\Mail\Factory::class);
// $app->alias('mailer', Illuminate\Mail\Mailer::class);
// $app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
// $app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);


///把Google的验证key放到环境变量
$google_key = dirname(__DIR__) . env('GOOGLE_APPLICATION_CREDENTIALS');
// $_ENV['GOOGLE_APPLICATION_CREDENTIALS'] = $google_key;
putenv("GOOGLE_APPLICATION_CREDENTIALS=".$google_key);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    App\Http\Middleware\CorsMiddleware::class
    
]);

$app->routeMiddleware([
    'auth'                  => App\Http\Middleware\Authenticate::class,
    'auth_admin'            => App\Http\Middleware\AuthenticateAdmin::class,
    'callback_auth'         => App\Http\Middleware\CallbackAuth::class,
    'cache.headers'         => \Illuminate\Http\Middleware\SetCacheHeaders::class,
    'throttle'              => \LumenRateLimiting\ThrottleRequests::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/
$app->register(Anik\Form\FormRequestServiceProvider::class);
$app->register(Jenssegers\Agent\AgentServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);   
$app->register(PHPOpenSourceSaver\JWTAuth\Providers\LumenServiceProvider::class);    //  JWT 用户认证
$app->register(App\Providers\EventServiceProvider::class);              //  Event处理
$app->register(Intervention\Image\ImageServiceProviderLumen::class);    //  Image处理
$app->register(\Laravel\Tinker\TinkerServiceProvider::class);
$app->register(Telegram\Bot\Laravel\TelegramServiceProvider::class);    //  telegram_bot
$app->register(Illuminate\Notifications\NotificationServiceProvider::class);    ///notification通知类
$app->register(Illuminate\Mail\MailServiceProvider::class);             //  邮件服务
$app->register(Laravel\Scout\ScoutServiceProvider::class);              //  scout全文检索
$app->register(Alaouy\Youtube\YoutubeServiceProvider::class);           //  youtube API服务
$app->register(App\Providers\BroadcastServiceProvider::class);          //  广播服务
$app->register(Illuminate\Redis\RedisServiceProvider::class);           //  redis

$app->register(App\Providers\Translation\TranslationServiceProvider::class);

// $app->register(Fedeisas\LaravelMailCssInliner\LaravelMailCssInlinerServiceProvider::class); ///邮件css处理服务
    
$app->register(App\Providers\AppServiceProvider::class);                //  所有的服务注册完以后调用AppServiceProvider



/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
