<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use App\DTOs\PredictionApiResponse;
use RuntimeException;
use UnexpectedValueException;

class PredictionApiClient
{
    /**
     * @return PredictionApiResponse
     */
    public function predict(UploadedFile $photoFile, string $inputCropName): PredictionApiResponse
    {
        $modelUrl = config('services.predict.endpoint');

        if (!is_string($modelUrl) || trim($modelUrl) === '') {
            throw new RuntimeException('AI 분석 서버 주소가 설정되지 않았습니다.');
        }

        $realPath = $photoFile->getRealPath();

        if (!is_string($realPath) || $realPath === '') {
            throw new RuntimeException('업로드 이미지 경로를 확인할 수 없습니다.');
        }

        $stream = fopen($realPath, 'rb');

        if ($stream === false) {
            throw new RuntimeException('업로드 이미지를 읽을 수 없습니다.');
        }

        try {
            $response = Http::connectTimeout(config('services.predict.connect_timeout'))
                ->timeout(config('services.predict.timeout'))
                ->acceptJson()
                ->attach(
                    'image',
                    $stream,
                    $photoFile->getClientOriginalName(),
                    ['Content-Type' => $photoFile->getMimeType()]
                )
                ->post($modelUrl, [
                    'crop_name' => $inputCropName,
                ]);
        } finally {
            fclose($stream);
        }

        if ($response->failed()) {
            throw new RuntimeException('AI 분석 서버가 실패 응답을 반환했습니다.');
        }

        $result = $response->json();

        if (!is_array($result)) {
            throw new UnexpectedValueException('AI 분석 서버가 잘못된 JSON 응답을 반환했습니다.');
        }

        if (!($result['success'] ?? false)) {
            throw new RuntimeException(
                $result['error']['message'] ?? $result['message'] ?? 'AI 예측 실패'
            );
        }

        $data = $result['data'] ?? null;

        if (!is_array($data)) {
            throw new UnexpectedValueException('AI prediction response data is missing.');
        }

        $cropName = $data['crop_name'] ?? '';
        $sickName = $data['sick_name_kor'] ?? '';
        $confidence = $data['confidence'] ?? null;

        if (
            !is_string($cropName)
            || !is_string($sickName)
            || !is_numeric($confidence)
            || trim($cropName) === ''
            || trim($sickName) === ''
            || mb_strlen($cropName) > 50
            || mb_strlen($sickName) > 255
            || (float) $confidence < 0
            || (float) $confidence > 100
        ) {
            throw new UnexpectedValueException('AI 분석 응답 형식이 올바르지 않습니다.');
        }

        return new PredictionApiResponse(
            cropName: trim($cropName),
            sickNameKor: trim($sickName),
            confidence: (float) $confidence,
        );
    }
}
