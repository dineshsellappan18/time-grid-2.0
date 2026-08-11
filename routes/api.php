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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

////////////////
// AJAX CALLS //
////////////////

Route::get('vacancies/{businessId}/{serviceId}', [
    'uses'       => 'AvailabilityController@getDates',
    'middleware' => ['throttle:availability'],
]);

Route::get('vacancies/{businessId}/{serviceId}/{date}', [
    'uses'       => 'AvailabilityController@getTimes',
    'middleware' => ['throttle:availability'],
]);
