<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\UserOpinionRequest;
use App\Http\Resources\ResultResource;
use App\Models\PredictionCache;
use App\Models\Train;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PredictController extends Controller
{
    public function store(FileUploadRequest $request)
    {
        $validated = $request->validated();
        /** @var UploadedFile $photoFile */
        $photoFile = $validated['image'];
        $cropName = $validated['cropName'];
        $hashname = hash_file('sha256', $photoFile->getRealPath());
        $cachedPrediction = PredictionCache::where('crop_name', $cropName)
            ->where('hashname', $hashname)
            ->first();
        $cacheHit = $cachedPrediction !== null;
        $path = null;

        try {
            if (!$cachedPrediction) {
                [$cropName, $sickName, $confidence] = $this->requestPrediction(
                    config('services.predict.endpoint'),
                    $photoFile,
                    $validated['cropName']
                );

                $cachedPrediction = PredictionCache::firstOrCreate(
                    [   'hashname' => $hashname ,
                        'crop_name' => $cropName,],
                    [
                        'sick_name' => $sickName,
                        'confidence' => $confidence,
                    ]
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

            return response()->json([
                'message' => $cacheHit
                    ? '캐시된 분석 결과를 사용했습니다.'
                    : '업로드 및 분석이 완료되었습니다.',
                'cache_hit' => $cacheHit,
                'data' => new ResultResource($photo),
            ], 201);
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
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

    public function opinionStore(UserOpinionRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $train = $request->user()->trains()->findOrFail($id);
            $train->user_opinion = $validated['cropName'] . '_' . $validated['sickNameKor'];
            $train->save();

            return response()->json(['message' => '의견이 반영되었습니다'], 200);
        } catch (Throwable $e) {
            Log::error('의견 전송 실패', [
                'user_id' => $request->user()?->id,
                'train_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => '의견 전송에 실패했습니다. 잠시 후 다시 시도해주세요.',
            ], 500);
        }
    }

    protected function requestPrediction(
        string $modelUrl,
        UploadedFile $photoFile,
        string $inputCropName
    ): array {
        $response = Http::timeout(30)
            ->attach(
                'image',
                file_get_contents($photoFile->getRealPath()),
                $photoFile->getClientOriginalName()
            )
            ->post($modelUrl, [
                'cropName' => $inputCropName,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('AI 분석 서버가 실패 응답을 반환했습니다.');
        }

        $cropName = $response->json('cropName');
        $sickName = $response->json('sickNameKor');
        $confidence = $response->json('confidence');

        if (!is_string($cropName) || !is_string($sickName) || !is_numeric($confidence)) {
            throw new \UnexpectedValueException('AI 분석 응답 형식이 올바르지 않습니다.');
        }

        return [$cropName, $sickName, (float) $confidence];
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
