<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\UserOpinionRequest;
use App\Http\Resources\ResultResource;
use App\Models\PredictionCache;
use App\Models\Train;
use App\Enums\CropName;
use App\Services\PredictionApiClient;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PredictController extends Controller
{
    public function __construct(
        private readonly PredictionApiClient $predictionApiClient
    ) {
    }

    public function store(FileUploadRequest $request)
    {
        $validated = $request->validated();
        /** @var UploadedFile $photoFile */
        $photoFile = $validated['image'];
        $cropName = $validated['cropName'];
        $hashname = null;
        $path = null;

        try {
            $hashname = hash_file('sha256', $photoFile->getRealPath());

            if (!is_string($hashname)) {
                throw new \RuntimeException('업로드 이미지 해시 생성에 실패했습니다.');
            }

            $cachedPrediction = $this->findCachedPrediction($hashname, $cropName);
            $cacheHit = $cachedPrediction !== null;

            if (!$cachedPrediction) {
                [$cachedPrediction, $cacheHit] = $this->resolvePrediction(
                    $hashname,
                    $cropName,
                    $photoFile
                );
            }

            $path = $this->storeImage($photoFile);

            $photo = DB::transaction(function () use (
                $request,
                $photoFile,
                $path,
                $cachedPrediction
            ) {
                return $this->storePhoto(
                    $path,
                    $photoFile,
                    $request,
                    $cachedPrediction
                );
            });

            return (new ResultResource($photo->loadMissing('predictionCache')))
                ->additional([
                    'message' => $cacheHit
                        ? '캐시된 분석 결과를 사용했습니다.'
                        : '업로드 및 분석이 완료되었습니다.',
                    'cache_hit' => $cacheHit,
                ])
                ->response()
                ->setStatusCode(201);
        } catch (LockTimeoutException $e) {
            Log::warning('동일 이미지 분석 요청 대기 시간 초과', [
                'user_id' => $request->user()?->id,
                'hashname' => $hashname,
                'crop_name' => $cropName,
            ]);

            return response()->json([
                'error' => '동일한 이미지가 분석 중입니다. 잠시 후 다시 시도해주세요.',
            ], 503);
        } catch (Throwable $e) {
            if ($path) {
                try {
                    Storage::disk('public')->delete($path);
                } catch (Throwable $deleteException) {
                    Log::warning('실패한 분석 이미지 정리 중 오류 발생', [
                        'file' => $path,
                        'error' => $deleteException->getMessage(),
                    ]);
                }
            }

            Log::error('AI 분석 처리 실패', [
                'user_id' => $request->user()?->id,
                'hashname' => $hashname,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'AI 분석 중 오류가 발생했습니다.',
            ], 500);
        }
    }

    private function findCachedPrediction(
        string $hashname,
        string $cropName
    ): ?PredictionCache {
        return PredictionCache::where('hashname', $hashname)
            ->where('crop_name', $cropName)
            ->first();
    }

    /**
     * 같은 이미지가 동시에 들어오면 AI 요청은 한 번만 보냄
     *
     * @return array{0: PredictionCache, 1: bool}
     */
    private function resolvePrediction(
        string $hashname,
        string $cropName,
        UploadedFile $photoFile
    ): array {
        $lockKey = 'prediction:' . hash('sha256', $hashname . '|' . $cropName);

        return Cache::lock($lockKey, 45)->block(40, function () use (
            $hashname,
            $cropName,
            $photoFile
        ) {
            // 기다리는 동안 먼저 끝난 요청이 있는지 다시 확인
            $cachedPrediction = $this->findCachedPrediction($hashname, $cropName);

            if ($cachedPrediction) {
                return [$cachedPrediction, true];
            }

            $result = $this->predictionApiClient->predict(
                $photoFile,
                $cropName
            );

            $expectedCropName = CropName::from($cropName)->korean();

            if ($result->cropName !== $expectedCropName) {
                Log::warning('AI 응답 작물명이 요청값과 다름', [
                    'requested' => $cropName,
                    'responded' => $result->cropName,
                    'hashname' => $hashname,
                ]);
            }

            // 캐시 기준은 사용자가 선택한 작물명으로 통일
            $prediction = PredictionCache::firstOrCreate(
                [
                    'hashname' => $hashname,
                    'crop_name' => $cropName,
                ],
                [
                    'sick_name' => $result->sickNameKor,
                    'confidence' => $result->confidence,
                ]
            );

            return [$prediction, !$prediction->wasRecentlyCreated];
        });
    }

    public function opinionStore(UserOpinionRequest $request, $id)
    {
        $validated = $request->validated();

        $train = $request->user()
            ->trains()
            ->findOrFail($id);

        $train->update([
            'user_opinion' => $validated['cropName'] . '_' . $validated['sickNameKor'],
        ]);

        return response()->json([
            'message' => '의견이 반영되었습니다',
        ], 200);
    }

    protected function storeImage(UploadedFile $photoFile): string
    {
        $path = $photoFile->storeAs(
            'images',
            $photoFile->hashName(),
            'public'
        );

        if (!is_string($path)) {
            throw new \RuntimeException('업로드 이미지 저장에 실패했습니다.');
        }

        return $path;
    }

    protected function storePhoto(
        string $path,
        UploadedFile $photoFile,
        Request $request,
        PredictionCache $cachedPrediction
    ): Train {
        $photo = new Train([
            'url' => Storage::disk('public')->url($path),
            'original_name' => $photoFile->getClientOriginalName(),
        ]);

        $photo->user()->associate($request->user());
        $photo->predictionCache()->associate($cachedPrediction);
        $photo->saveOrFail();

        return $photo;
    }
}
