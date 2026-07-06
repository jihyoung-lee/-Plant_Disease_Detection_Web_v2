<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PredictionResultResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PredictionResultController extends Controller
{
    public function index(Request $request)
    {
        $predictionRecords = $request->user()
            ->trains()
            ->with('predictionCache')
            ->latest()
            ->paginate(6);

        return PredictionResultResource::collection($predictionRecords);
    }

    public function show(Request $request, int $id)
    {
        $predictionRecord = $request->user()
            ->trains()
            ->with('predictionCache')
            ->find($id);

        if ($predictionRecord === null) {
            return response()->json([
                'error' => '해당 결과를 찾을 수 없거나 권한이 없습니다.',
            ], 404);
        }

        return new PredictionResultResource($predictionRecord);
    }

    public function destroy(Request $request, int $id)
    {
        $predictionRecord = $request->user()
            ->trains()
            ->find($id);

        if ($predictionRecord === null) {
            return response()->json([
                'error' => '해당 결과를 찾을 수 없거나 권한이 없습니다.',
            ], 404);
        }

        $storagePath = $this->storagePathFromUrl($predictionRecord->url);

        if ($storagePath !== null && Storage::disk('public')->exists($storagePath)) {
            try {
                Storage::disk('public')->delete($storagePath);
            } catch (Throwable $exception) {
                Log::warning('이미지 삭제 실패', [
                    'file' => $storagePath,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $predictionRecord->delete();

        return response()->json([
            'message' => '삭제 완료',
        ]);
    }

    private function storagePathFromUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        $urlPath = parse_url($url, PHP_URL_PATH);

        if (! is_string($urlPath)) {
            return null;
        }

        $storagePrefix = '/storage/';
        $prefixPosition = strpos($urlPath, $storagePrefix);

        if ($prefixPosition === false) {
            return null;
        }

        return ltrim(
            substr($urlPath, $prefixPosition + strlen($storagePrefix)),
            '/'
        );
    }
}
