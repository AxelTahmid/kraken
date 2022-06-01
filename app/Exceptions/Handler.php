<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponseTrait;
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     *
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        $ignoreable_exception_messages = ['Unauthenticated or Token Expired, Please Login'];
        //$ignoreable_exception_messages[] = 'The refresh token is invalid.';
        $ignoreable_exception_messages[] = 'The resource owner or authorization server denied the request.';

        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            if (!in_array($exception->getMessage(), $ignoreable_exception_messages)) {
                app('sentry')->captureException($exception);
            }
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {

        if ($exception instanceof PostTooLargeException) {
            return $this->errorResponse("Size of attached file should be less " . ini_get("upload_max_filesize") . "B", 400);
        };
        if ($exception instanceof AuthenticationException) {
            return $this->errorResponse('Unauthenticated or Token Expired, Please Login', 401);
        };
        if ($exception instanceof ThrottleRequestsException) {
            return $this->errorResponse('Too Many Requests,Please Slow Down', 429);
        };
        if ($exception instanceof ModelNotFoundException) {
            return $this->errorResponse('Entry for ' . str_replace('App\\', '', $exception->getModel()) . ' not found', 404);
        };
        if ($exception instanceof ValidationException) {
            return $this->errorResponse($exception->getMessage(), 422);
        };
        if ($exception instanceof QueryException) {
            return $this->errorResponse('There was Issue with the Query', 500);
        };
        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('The specified URL cannot be found', 404);
        }
        if ($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse('The specified method for the request is invalid', 405);
        }
        if (config('app.debug')) {
            return parent::render($request, $exception);
        }
        if ($exception instanceof \Error) {
            return $this->errorResponse('Unexpected Exception. Try later', 500);
        };
        return parent::render($request, $exception);
    }
}
