<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     * 
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*')) {
            if ($exception instanceof AuthenticationException) {
                return $this->unauthenticated($request, $exception);
                /*
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
                */
            }
        }
        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception) // Claude AI solution for receiving 401 instead of 500
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated. Invalid or missing token.',
            ], 401);
        }
        return parent::unauthenticated($request, $exception);
    }
}
