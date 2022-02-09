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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::namespace('App\Http\Controllers\API')->group(function () {
     Route::post('register', 'ApiAuthController@register');
     Route::post('login', 'ApiAuthController@login');

     Route::middleware('auth:api')->group(function () {
         Route::get('me', 'ApiAuthController@me');
         Route::get('get-refresh-token', 'ApiAuthController@refresh');
         Route::get('logout', 'ApiAuthController@logout');
     });
});
