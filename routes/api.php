<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/state/{state}/cities', 'ApiController@getCities')->name('cities');

Route::post('/user/{userId}/visits', 'ApiController@userVisit')->name('visit');
Route::get('/user/{userId}/visits', 'ApiController@userCities')->name('user_visit');
Route::delete('/user/{userId}/visits/{visitId}', 'ApiController@userDeleteCity');

Route::get('/user/{userId}/visits/states', 'ApiController@userStates')->name('user_visit_state');

