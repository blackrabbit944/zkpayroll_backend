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

    $router->get('/init/login_user', 'InitController@loginUser');

});

$router->group(['prefix' => 'v1', 'middleware' => ['callback_auth']], function() use($router) {
    // $router->post('/callback/erc721_transfer_event', 'CallbackController@ERC721TransferEvent');
    // $router->post('/callback/nftclub_mint_event', 'CallbackController@NFTClubMintEvent');
});



///需要登录验证的api
$router->group(['prefix' => 'v1','middleware' => ['auth']], function () use ($router) {
    
    $router->post('/user/logout', 'UserController@logout');
    $router->patch('/user/update', 'UserController@update');
    $router->patch('/user/switch_language', 'UserController@switchLanguage');

    $router->post('/salary/add','SalaryController@add');
    $router->delete('/salary/delete','SalaryController@delete');
    $router->patch('/salary/update','SalaryController@update');
    $router->get('/salary/load', 'SalaryController@load');
    $router->get('/salary/list', 'SalaryController@list');


    $router->post('/steaming_salary/add','SteamingSalaryController@add');
    $router->delete('/steaming_salary/delete','SteamingSalaryController@delete');
    $router->patch('/steaming_salary/update','SteamingSalaryController@update');
    $router->get('/steaming_salary/load', 'SteamingSalaryController@load');
    $router->get('/steaming_salary/list', 'SteamingSalaryController@list');

});


///需要管理员验证的api
$router->group(['prefix' => 'v1','middleware' => 'auth_admin'], function () use ($router) {


});

$router->group([], function () use( $router) {
    // $router->get('{contract_address:0x[0-9a-f]{40}}/{token_id:[0-9]+}', 'MiscController@Erc721Metadata');
});

