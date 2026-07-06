<?php

namespace App\Exceptions;

use App\Exceptions\Prediction\PredictionNetworkException;
use App\Exceptions\Prediction\PredictionResponseFormatException;
use App\Exceptions\Prediction\PredictionUpstreamException;
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
        $this->renderable(function (PredictionNetworkException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request failed.',
                'data' => null,
                'error' => [
                    'code' => 'PREDICTION_SERVICE_UNAVAILABLE',
                    'message' => 'AI 분석 서버에 연결할 수 없습니다.',
                ],
            ], 503);
        });

        $this->renderable(function (PredictionResponseFormatException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request failed.',
                'data' => null,
                'error' => [
                    'code' => 'INVALID_PREDICTION_RESPONSE',
                    'message' => 'AI 분석 서버에서 올바른 응답을 받지 못했습니다.',
                ],
            ], 502);
        });

        $this->renderable(function (PredictionUpstreamException $e) {
            $status = $e->responseStatus();

            return response()->json([
                'success' => false,
                'message' => 'Request failed.',
                'data' => null,
                'error' => [
                    'code' => $e->errorCode ?? 'PREDICTION_SERVICE_ERROR',
                    'message' => $status === 502
                        ? 'AI 분석 서버에서 오류가 발생했습니다.'
                        : $e->getMessage(),
                ],
            ], $status);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
