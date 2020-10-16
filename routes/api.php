<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('reset_password', 'AuthController@reset');
});


Route::group([
    'middleware' => 'api',
    'prefix' => 'poll'
], function($router) {
    Route::get('/', 'PollController@index');
    Route::get('{id}', 'PollController@show');
    Route::post('/', 'PollController@store');
    Route::delete('{id}', 'PollController@destroy');
    Route::post('{id}/vote/{choice_id}', 'PollController@vote');
});
