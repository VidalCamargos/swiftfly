<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (Router $router):void {
    $router->get('/ping', fn() => 'pong')->name('ping');

    $router->group(['prefix' => 'auth'], function (Router $router) {
        $router->post('/register', [AuthController::class, 'register'])->name('auth.register');
        $router->post('/login', [AuthController::class, 'login'])->name('auth.login');

        $router->group(['middleware' => 'auth:api'], function (Router $router) {
            $router->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        });
    });
});

