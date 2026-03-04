<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (Router $router):void {
    $router->get('/ping', fn() => 'pong')->name('ping');
});

