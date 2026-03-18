<?php

use App\Http\Middleware\ResolveAcademy;
use App\Http\Middleware\StudentCheckMiddleware;
// use App\Http\Middleware\ParentSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        $middleware->validateCsrfTokens(
            except: ['juso-popup']
        );

        $middleware->trustProxies(at: '*');

        // 서브도메인에서 학원 식별 (모든 웹 요청)
        $middleware->web(append: [
            ResolveAcademy::class,
        ]);

        // 커스텀 미들웨어 등록
        $middleware->alias([
            'student.check' => StudentCheckMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/login',
        ]);

        // 글로벌 미들웨어 등록
        // $middleware->append([
        //     \App\Http\Middleware\ParentSession::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
