<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

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
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // return parent::render($request, $exception);

        // dd($request, $exception);
        $exceptionMessage = [
            'statusCode' => "4001",
            'message' => '請聯絡開發人員',
            'payload' => (object) [],
        ];

        $exceptionMessage['statusCode'] = (string)$exception->getCode();
        $exceptionMessage['message'] = $exception->getMessage();
        $exceptionMessage['error'] = $exception->getTraceAsString();

        if ($exception->getCode() == 0 || !empty($exception->getPrevious())) {
            $exceptionMessage['statusCode'] = (string)4001;
        }

        if (!in_array(env('APP_ENV'), ['production', 'staging', 'develop']) || env('APP_DEBUG')) {
            return response()->json($exceptionMessage);
        };

        if (in_array($exceptionMessage['statusCode'], ["4001", "500"])) {
            $exceptionMessage['message'] =  '請聯絡開發人員';
        }

        unset($exceptionMessage['error']);

        return response()->json($exceptionMessage);
    }
}
