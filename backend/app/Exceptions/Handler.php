<?php

namespace App\Exceptions;

use App\Exceptions\Prediction\PredictionNetworkException;
use App\Exceptions\Prediction\PredictionResponseFormatException;
use App\Exceptions\Prediction\PredictionUpstreamException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
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
        $this->renderable(function (ValidationException $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => '입력값이 올바르지 않습니다.',
                'data' => null,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => '입력값이 올바르지 않습니다.',
                    'details' => $e->errors(),
                ],
            ], $e->status);
        });

        $this->renderable(function (PredictionNetworkException $e) {
            return $this->predictionErrorResponse(
                status: 503,
                code: 'PREDICTION_SERVICE_UNAVAILABLE',
                message: 'AI 분석 서버에 연결할 수 없습니다.'
            );
        });

        $this->renderable(function (PredictionResponseFormatException $e) {
            return $this->predictionErrorResponse(
                status: 502,
                code: 'PREDICTION_RESPONSE_INVALID',
                message: 'AI 분석 서버에서 올바른 응답을 받지 못했습니다.'
            );
        });

        $this->renderable(function (PredictionUpstreamException $e) {
            $status = $e->responseStatus();

            return $this->predictionErrorResponse(
                status: $status,
                code: $this->predictionUpstreamErrorCode($e, $status),
                message: $this->predictionUpstreamErrorMessage($e, $status)
            );
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }

    private function predictionErrorResponse(int $status, string $code, string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $status);
    }

    private function predictionUpstreamErrorCode(PredictionUpstreamException $exception, int $status): string
    {
        if (
            in_array($status, [400, 413, 415, 422], true)
            && is_string($exception->errorCode)
            && trim($exception->errorCode) !== ''
        ) {
            return $exception->errorCode;
        }

        return match ($status) {
            400 => 'PREDICTION_UPSTREAM_BAD_REQUEST',
            413 => 'PREDICTION_IMAGE_TOO_LARGE',
            415 => 'PREDICTION_IMAGE_TYPE_UNSUPPORTED',
            422 => 'PREDICTION_UPSTREAM_VALIDATION_FAILED',
            default => 'PREDICTION_UPSTREAM_ERROR',
        };
    }

    private function predictionUpstreamErrorMessage(PredictionUpstreamException $exception, int $status): string
    {
        if (in_array($status, [400, 413, 415, 422], true) && trim($exception->getMessage()) !== '') {
            return $exception->getMessage();
        }

        return match ($status) {
            400 => '예측 요청 값이 올바르지 않습니다.',
            413 => '업로드 이미지 용량이 너무 큽니다.',
            415 => '지원하지 않는 이미지 형식입니다.',
            422 => '예측 요청을 처리할 수 없습니다.',
            default => 'AI 분석 서버에서 오류가 발생했습니다.',
        };
    }
}
