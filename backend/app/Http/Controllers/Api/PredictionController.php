<?php

namespace App\Http\Controllers\Api;

use App\Enums\CropName;
use App\Exceptions\Prediction\PredictionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PredictionRequest;
use App\Http\Requests\UserOpinionRequest;
use App\Http\Resources\PredictionResultResource;
use App\Models\User;
use App\Services\PredictionService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

class PredictionController extends Controller
{
    public function __construct(
        private readonly PredictionService $predictionService,
    ) {
    }

    public function store(PredictionRequest $request)
    {
        $validated = $request->validated();

        /** @var UploadedFile $image */
        $image = $validated['image'];
        $cropCode = $validated['cropName'];

        /** @var User $user */
        $user = $request->user();

        try {
            $serviceResult = $this->predictionService->predict(
                user: $user,
                image: $image,
                crop: CropName::from($cropCode),
            );

            return (new PredictionResultResource(
                $serviceResult->predictionRecord->loadMissing('predictionCache')
            ))
                ->additional([
                    'message' => $serviceResult->cacheHit
                        ? '캐시된 분석 결과를 사용했습니다.'
                        : '업로드 및 분석이 완료되었습니다.',
                    'cache_hit' => $serviceResult->cacheHit,
                ])
                ->response()
                ->setStatusCode(201);
        } catch (LockTimeoutException $exception) {
            Log::warning('동일 이미지 분석 요청 대기 시간 초과', [
                'user_id' => $user->id,
                'crop_code' => $cropCode,
            ]);

            return $this->predictionErrorResponse(
                status: 503,
                code: 'PREDICTION_REQUEST_LOCK_TIMEOUT',
                message: '동일한 이미지가 분석 중입니다. 잠시 후 다시 시도해 주세요.'
            );
        } catch (PredictionException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('AI 분석 처리 실패', [
                'user_id' => $user->id,
                'crop_code' => $cropCode,
                'error' => $exception->getMessage(),
            ]);

            return $this->predictionErrorResponse(
                status: 500,
                code: 'PREDICTION_PROCESSING_FAILED',
                message: 'AI 분석 중 오류가 발생했습니다.'
            );
        }
    }

    public function storeOpinion(UserOpinionRequest $request, int $id)
    {
        $validated = $request->validated();

        $predictionRecord = $request->user()
            ->trains()
            ->findOrFail($id);

        $predictionRecord->update([
            'user_opinion' => $validated['cropName'].'_'.$validated['sickNameKor'],
        ]);

        return response()->json([
            'message' => '의견을 반영했습니다.',
        ]);
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
}
