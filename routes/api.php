<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\leaderboardController;
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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::group([
	'middleware' => 'api',
	'prefix' => 'v1'
], function ($router) {
	Route::post('/user/signin', [UserController::class, 'signIn']);
	Route::post('/user/signup', [UserController::class, 'signUp']);
	Route::post('/endgame', [GameController::class, 'endGame']);
	Route::get('/leaderboard', [leaderboardController::class, 'list']);
});