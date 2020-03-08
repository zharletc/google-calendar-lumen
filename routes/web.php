<?php

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

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get("/get/auth/url", "GCalendarController@getAuthUrl");
    $router->get("/get/access/token", "GCalendarController@getAccessToken");
    $router->post("/get/calendar", "GCalendarController@getCalendar");
    $router->post("/insert/event", "GCalendarController@insertEvent");
});
