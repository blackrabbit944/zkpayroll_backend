<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

///普通的api
$router->group(['prefix' => 'v1'], function () use ($router) {

    $router->get('/init', 'IndexController@init');
    $router->get('/info', 'IndexController@info');
    $router->get('/test','IndexController@test');
    $router->get('/status','IndexController@websiteStatus');
    $router->get('/index', 'IndexController@home');

    ///user相关
    $router->post('/user/login', 'UserController@login');
    $router->post('/user/fake_login', 'UserController@fakeLogin');
    $router->get('/user/load', 'UserController@load');

    $router->get('/collection/load', 'CollectionController@load');
    $router->get('/collection/list', 'CollectionController@list');
    $router->get('/collection/hot_list', 'CollectionController@hotList');


    $router->get('/item/load', 'ItemController@load');
    $router->get('/item/image', 'ItemController@image');
    $router->get('/item/list', 'ItemController@list');

    $router->get('/order/load', 'OrderController@load');
    $router->post('/order/get_buy_sign', 'OrderController@getBuySign');
    $router->get('/order/list', 'OrderController@list');
    
    $router->get('/order/validate_owner', 'OrderController@validateOwner');

    $router->get('/item_history/list', 'ItemHistoryController@list');
    $router->get('/statistic', 'InitController@statistic');

    $router->get('/user/analytics/whitelist_data', 'UserAnalyticsController@whitelistData');

    $router->get('/init/login_user', 'InitController@loginUser');

    $router->get('/link/list', 'LinkController@list');
    $router->get('/link/load', 'LinkController@load');
    $router->get('/link/all_list', 'LinkController@allList');
   
    $router->get('/profile/load','ProfileController@load');

    $router->get('/club/load','ClubController@load');
    $router->get('/club/list','ClubController@list');
    $router->get('/club/invite_link', 'ClubController@getInviteLink');

    $router->get('/discord/user_callback','DiscordController@userCallback');
    $router->get('/discord/guild_callback','DiscordController@guildCallback');

    $router->get('/discord/test','DiscordController@test');

    $router->get('/nftassets/image','NftController@image');

    $router->get('/post/load','PostController@load');
    $router->get('/post/list','PostController@list');

});

$router->group(['prefix' => 'v1', 'middleware' => ['callback_auth']], function() use($router) {
    $router->post('/callback/erc721_transfer_event', 'CallbackController@ERC721TransferEvent');
    $router->post('/callback/nftclub_mint_event', 'CallbackController@NFTClubMintEvent');
});



///需要登录验证的api
$router->group(['prefix' => 'v1','middleware' => ['auth']], function () use ($router) {
    
    $router->post('/user/logout', 'UserController@logout');
    $router->patch('/user/update', 'UserController@update');
    $router->patch('/user/switch_language', 'UserController@switchLanguage');

    $router->patch('/user/analytics/set', 'UserAnalyticsController@set');
    $router->get('/user/analytics/list', 'UserAnalyticsController@list');
    $router->get('/user/analytics/load', 'UserAnalyticsController@load');

    $router->post('/upload/img', 'UploadController@img');

    $router->post('/order/add', 'OrderController@add');
    $router->patch('/order/update', 'OrderController@update');
    
    $router->delete('/order/delete', 'OrderController@delete');
    $router->delete('/order/delete_by_owner', 'OrderController@deleteByOwner');
    
    $router->get('/item/my_list', 'ItemController@myList');

    $router->post('/link/add', 'LinkController@add');
    $router->patch('/link/update', 'LinkController@update');
    $router->delete('/link/delete', 'LinkController@delete');
    $router->post('/link/sort', 'LinkController@sort');

    //profile
    $router->patch('/profile/set','ProfileController@set');

    //club
    $router->post('/club/add','ClubController@add');
    $router->patch('/club/update', 'ClubController@update');
    $router->delete('/club/delete', 'ClubController@delete');

    //discord
    $router->post('/discord/club_info', 'DiscordController@getClubInfo');
    $router->get('/discord/user_info', 'DiscordController@getBindUser');
    $router->get('/discord/verify_nft','DiscordController@verifyNft');

    $router->get('/discord/user_guilds', 'DiscordController@getUserOwnGuilds');

    //club_user对应的代码
    $router->get('/club/user/list','ClubUserController@list');
    $router->get('/club/user/load','ClubUserController@load');
    
    $router->get('/discord_log/list','DiscordLogController@list');
    $router->get('/tx/list','TxController@list');

    $router->post('/post/add','PostController@add');
    $router->delete('/post/delete','PostController@delete');
    $router->patch('/post/update','PostController@update');

});


///需要管理员验证的api
$router->group(['prefix' => 'v1','middleware' => 'auth_admin'], function () use ($router) {
    $router->post('/collection/add', 'CollectionController@add');
    $router->patch('/collection/update', 'CollectionController@update');
    $router->delete('/collection/delete', 'CollectionController@delete');

    $router->post('/item/add', 'ItemController@add');
    $router->patch('/item/update', 'ItemController@update');

});

$router->group([], function () use( $router) {
    $router->get('{contract_address:0x[0-9a-f]{40}}/{token_id:[0-9]+}', 'MiscController@Erc721Metadata');
});

