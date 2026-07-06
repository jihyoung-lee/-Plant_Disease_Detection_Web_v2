<?php

namespace App\Services;

use App\DTOs\PredictionServiceResult;
use App\Enums\CropName;
use App\Models\PredictionCache;
use App\Models\Train;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class PredictionService
{
    public function __construct(
        private readonly PredictionApiClient $predictionApiClient,
    ) {
    }

    public function predict(
        User $user,
        UploadedFile $image,
        CropName $crop,
    ): PredictionServiceResult {
        $imageHash = $this->createImageHash($image);
        $predictionCache = $this->findPredictionCache($imageHash, $crop);
        $cacheHit = $predictionCache !== null;

        if ($predictionCache === null) {
            [$predictionCache, $cacheHit] = $this->resolvePredictionCache(
                imageHash: $imageHash,
                crop: $crop,
                image: $image,
            );
        }

        $imagePath = null;

        try {
            $imagePath = $this->storeImage($image);

            $predictionRecord = DB::transaction(
                fn (): Train => $this->storePredictionRecord(
                    imagePath: $imagePath,
                    image: $image,
                    user: $user,
                    predictionCache: $predictionCache,
                )
            );
        } catch (Throwable $exception) {
            if ($imagePath !== null) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }

        return new PredictionServiceResult(
            predictionRecord: $predictionRecord,
            cacheHit: $cacheHit,
        );
    }

    private function createImageHash(UploadedFile $image): string
    {
        $imageHash = hash_file('sha256', $image->getRealPath());

        if (! is_string($imageHash)) {
            throw new RuntimeException('업로드 이미지 해시 생성에 실패했습니다.');
        }

        return $imageHash;
    }

    /**
     * 동일한 이미지가 동시에 요청되면 AI 서버에는 한 번만 요청합니다.
     *
     * @return array{0: PredictionCache, 1: bool}
     */
    private function resolvePredictionCache(
        string $imageHash,
        CropName $crop,
        UploadedFile $image,
    ): array {
        $cropCode = $crop->value;
        $lockKey = 'prediction:'.hash('sha256', $imageHash.'|'.$cropCode);

        return Cache::lock($lockKey, 45)->block(40, function () use (
            $imageHash,
            $crop,
            $cropCode,
            $image,
        ): array {
            $predictionCache = $this->findPredictionCache($imageHash, $crop);

            if ($predictionCache !== null) {
                return [$predictionCache, true];
            }

            $apiResponse = $this->predictionApiClient->predict($image, $cropCode);
            $expectedCropNameKor = $crop->koreanName();

            if ($apiResponse->cropNameKor !== $expectedCropNameKor) {
                Log::warning('AI 응답 작물명이 요청값과 다름', [
                    'requested' => $expectedCropNameKor,
                    'responded' => $apiResponse->cropNameKor,
                    'image_hash' => $imageHash,
                ]);
            }

            $predictionCache = PredictionCache::firstOrCreate(
                [
                    'hashname' => $imageHash,
                    'crop_name' => $cropCode,
                ],
                [
                    'sick_name' => $apiResponse->sickNameKor,
                    'confidence' => $apiResponse->confidence,
                ]
            );

            return [$predictionCache, ! $predictionCache->wasRecentlyCreated];
        });
    }

    private function findPredictionCache(
        string $imageHash,
        CropName $crop,
    ): ?PredictionCache {
        return PredictionCache::query()
            ->where('hashname', $imageHash)
            ->where('crop_name', $crop->value)
            ->first();
    }

    private function storeImage(UploadedFile $image): string
    {
        $imagePath = $image->storeAs(
            'images',
            $image->hashName(),
            'public'
        );

        if (! is_string($imagePath)) {
            throw new RuntimeException('업로드 이미지 저장에 실패했습니다.');
        }

        return $imagePath;
    }

    private function storePredictionRecord(
        string $imagePath,
        UploadedFile $image,
        User $user,
        PredictionCache $predictionCache,
    ): Train {
        $predictionRecord = new Train([
            'url' => Storage::disk('public')->url($imagePath),
            'original_name' => $image->getClientOriginalName(),
        ]);

        $predictionRecord->user()->associate($user);
        $predictionRecord->predictionCache()->associate($predictionCache);
        $predictionRecord->saveOrFail();

        return $predictionRecord;
    }
}
