<?php

namespace App\Services;

use App\DTOs\PredictionApiResponse;
use App\Exceptions\Prediction\PredictionNetworkException;
use App\Exceptions\Prediction\PredictionResponseFormatException;
use App\Exceptions\Prediction\PredictionUpstreamException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PredictionApiClient
{
    public function predict(UploadedFile $image, string $cropCode): PredictionApiResponse
    {
        $endpoint = config('services.predict.endpoint');

        if (! is_string($endpoint) || trim($endpoint) === '') {
            throw new RuntimeException('AI 분석 서버 주소가 설정되지 않았습니다.');
        }

        $imagePath = $image->getRealPath();

        if (! is_string($imagePath) || $imagePath === '') {
            throw new RuntimeException('업로드 이미지 경로를 확인할 수 없습니다.');
        }

        $imageStream = fopen($imagePath, 'rb');

        $imageContents = file_get_contents($image->getRealPath());

        if ($imageContents === false) {
            throw new RuntimeException('업로드 이미지를 읽을 수 없습니다.');
        }

        try {
            $response = Http::connectTimeout(config('services.predict.connect_timeout'))
                ->timeout(config('services.predict.timeout'))
                ->acceptJson()
                ->retry(
                    3,
                    500,
                    function ($exception) {
                        if ($exception instanceof ConnectionException) {
                            return true;
                        }

                        if ($exception instanceof RequestException) {
                            return in_array(
                                $exception->response?->status(),
                                [500, 502, 503, 504]
                            );
                        }

                        return false;
                    },
                false)
                ->attach(
                    'image',
                    $imageContents,
                    $image->getClientOriginalName(),
                    ['Content-Type' => $image->getMimeType()]
                )
                ->post($endpoint, [
                    'crop_name' => $cropCode,
                ]);
        } catch (ConnectionException $exception) {
            throw new PredictionNetworkException(
                'AI 분석 서버에 연결할 수 없습니다.',
                previous: $exception
            );
        } finally {
            fclose($imageStream);
        }

        $responseBody = $response->json();

        if ($response->failed()) {
            throw $this->createUpstreamException($response, $responseBody);
        }

        if (! is_array($responseBody)) {
            throw new PredictionResponseFormatException(
                'AI 분석 서버가 잘못된 JSON 응답을 반환했습니다.'
            );
        }

        if (! ($responseBody['success'] ?? false)) {
            throw $this->createUpstreamException($response, $responseBody);
        }

        $predictionData = $responseBody['data'] ?? null;

        if (! is_array($predictionData)) {
            throw new PredictionResponseFormatException('AI 분석 응답 데이터가 없습니다.');
        }

        $cropNameKor = $predictionData['crop_name'] ?? '';
        $sickNameKor = $predictionData['sick_name_kor'] ?? '';
        $confidence = $predictionData['confidence'] ?? null;

        if (
            ! is_string($cropNameKor)
            || ! is_string($sickNameKor)
            || ! is_numeric($confidence)
            || trim($cropNameKor) === ''
            || trim($sickNameKor) === ''
            || mb_strlen($cropNameKor) > 50
            || mb_strlen($sickNameKor) > 255
            || (float) $confidence < 0
            || (float) $confidence > 100
        ) {
            throw new PredictionResponseFormatException('AI 분석 응답 형식이 올바르지 않습니다.');
        }

        return new PredictionApiResponse(
            cropNameKor: trim($cropNameKor),
            sickNameKor: trim($sickNameKor),
            confidence: (float) $confidence,
        );
    }

    private function createUpstreamException(
        Response $response,
        mixed $responseBody,
    ): PredictionUpstreamException {
        $error = is_array($responseBody) && is_array($responseBody['error'] ?? null)
            ? $responseBody['error']
            : [];

        $errorCode = $error['code'] ?? null;
        $message = $error['message']
            ?? (is_array($responseBody) ? ($responseBody['message'] ?? null) : null)
            ?? 'AI 분석 서버가 실패 응답을 반환했습니다.';

        return new PredictionUpstreamException(
            status: $response->status(),
            errorCode: is_string($errorCode) ? $errorCode : null,
            message: is_string($message) ? $message : 'AI 분석 서버 오류',
        );
    }
}
