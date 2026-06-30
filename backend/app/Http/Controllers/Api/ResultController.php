<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResultResource;
use App\Models\Train;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $results = $request->user()
            ->trains()
            ->with('predictionCache')
            ->latest()
            ->paginate(6);

        return ResultResource::collection($results);
    }

    public function show(Request $request, $id)
    {
        $result = $request->user()
            ->trains()
            ->with('predictionCache')
            ->find($id);

        if (!$result) {
            return response()->json([
                'error' => '해당 결과를 찾을 수 없거나 권한이 없습니다.',
            ], 404);
        }

        return new ResultResource($result);
    }

    public function destroy(Request $request, $id)
    {
        $result = $request->user()->trains()->find($id);

        if (!$result) {
            return response()->json([
                'error' => '해당 결과를 찾을 수 없거나 권한이 없습니다.',
            ], 404);
        }

        $storagePath = $this->storagePathFromUrl($result->url);

        if ($storagePath && Storage::disk('public')->exists($storagePath)) {
            try {
                Storage::disk('public')->delete($storagePath);
            } catch (Throwable $e) {
                Log::warning('이미지 삭제 실패', [
                    'file' => $storagePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $result->delete();

        return response()->json(['message' => '삭제 완료']);
    }

    protected function storagePathFromUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path)) {
            return null;
        }

        $marker = '/storage/';
        $position = strpos($path, $marker);

        if ($position === false) {
            return null;
        }

        return ltrim(substr($path, $position + strlen($marker)), '/');
    }

}
