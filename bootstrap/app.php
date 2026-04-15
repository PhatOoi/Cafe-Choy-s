<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// File bootstrap chính của Laravel app: khai báo route, middleware và exception config.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Gắn middleware đồng bộ thanh toán QR vào nhóm web để chạy sau mỗi request của khách.
        $middleware->web(append: [
            \App\Http\Middleware\SyncPaidQrOrderMiddleware::class,
        ]);

        // Đăng ký alias middleware để dùng ngắn gọn trong routes/web.php.
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'staff' => \App\Http\Middleware\StaffMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
