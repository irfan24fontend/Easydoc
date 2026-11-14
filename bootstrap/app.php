<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Behind cPanel / Cloudflare / a load balancer: trust X-Forwarded-* so Laravel knows the request was https.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'doctor.approved' => \App\Http\Middleware\EnsureDoctorApproved::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return response()->view('errors.generic', [
                'status' => 419,
                'title' => __('Session expired'),
                'message' => __('Your session expired. Please refresh the page and try again.'),
            ], 419);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $status = $exception->getStatusCode();
            [$title, $message] = match ($status) {
                400 => [__('Bad request'), __('The request could not be understood. Please try again.')],
                401 => [__('Sign in required'), __('Please sign in to continue.')],
                403 => [__('Access denied'), __('You do not have permission to access this page.')],
                404 => [__('Page not found'), __('The page you requested could not be found.')],
                405 => [__('Action not allowed'), __('This action is not available here.')],
                408 => [__('Request timed out'), __('The request took too long. Please try again.')],
                409 => [__('Request conflict'), __('The request conflicts with the current data. Please refresh and try again.')],
                410 => [__('Page no longer available'), __('This page is no longer available.')],
                413 => [__('Request too large'), __('The submitted content is too large. Please reduce it and try again.')],
                419 => [__('Session expired'), __('Your session expired. Please refresh the page and try again.')],
                422 => [__('Information could not be processed'), __('Check the submitted information and try again.')],
                429 => [__('Too many requests'), __('Please wait a moment before trying again.')],
                500 => [__('Server error'), __('A server error occurred. Please try again.')],
                502 => [__('Service connection error'), __('A service connection failed. Please try again shortly.')],
                503 => [__('Service unavailable'), __('The service is temporarily unavailable. Please try again shortly.')],
                504 => [__('Request timed out'), __('The service took too long to respond. Please try again shortly.')],
                default => [__('Request could not be completed'), __('Please try again or return to the previous page.')],
            };

            $headers = method_exists($exception, 'getHeaders') ? $exception->getHeaders() : [];

            return response()->view('errors.generic', compact('status', 'title', 'message'), $status, $headers);
        });
    })->create();
