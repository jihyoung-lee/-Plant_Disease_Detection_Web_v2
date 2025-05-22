<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function diseaseListApi(Request $request)
    {
        $searchType = $request->input('type', 1);
        $search = $request->input('search', '');
        $page = max(1, (int)$request->input('page', 1));
        $perPage = 5;

        $encodedSearch = urlencode($search);
        $cacheKey = "disease_list_{$searchType}_{$encodedSearch}";

        Log::info("🧩 요청 들어옴", [
            'cacheKey' => $cacheKey,
            'page' => $page,
            'search' => $search,
            'searchType' => $searchType
        ]);

        $items = Cache::remember($cacheKey, now()->addHours(6), function () use ($searchType, $search) {
            Log::info("🌀 캐시 MISS → API 호출 시작", [
                'searchType' => $searchType,
                'search' => $search
            ]);

            $params = [
                'serviceCode' => 'SVC01',
                'serviceType' => 'AA001:JSON',
                'displayCount' => 100,
                'startPoint' => 1,
            ];

            if ($searchType == 1) {
                $params['cropName'] = $search;
            } else {
                $params['sickNameKor'] = $search;
            }

            $response = self::callApi($params);

            Log::info("✅ API 응답 완료", [
                '결과수' => count($response['service']['list'] ?? [])
            ]);

            return $response['service']['list'] ?? [];
        });

        Log::info("📦 캐시 HIT 또는 저장됨", [
            'cacheKey' => $cacheKey,
            '총개수' => count($items)
        ]);

        $total = count($items);
        $offset = ($page - 1) * $perPage;
        $pagedItems = array_slice($items, $offset, $perPage);

        return response()->json([
            'data' => $pagedItems,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ]
        ]);
    }

    public function infoApi(Request $request)
    {
        $cropName = $request->input('cropName');
        $sickNameKor = $request->input('sickNameKor');

        $encodedCropName = urlencode($cropName);
        $encodedSickNameKro = urlencode($sickNameKor);
        $cacheKey = "disease_info:{$encodedCropName}:{$encodedSickNameKro}";

        try {
            $response = Cache::remember($cacheKey, now()->addHours(6), function () use ($cropName, $sickNameKor) {
                return self::info($cropName, $sickNameKor);
            });

            if (is_object($response) && method_exists($response, 'toArray')) {
                $response = $response->toArray();
            } elseif ($response instanceof \JsonSerializable) {
                $response = json_decode(json_encode($response), true);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'API 호출 실패',
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'cropName' => $cropName,
            'sickNameKor' => $sickNameKor,
            'raw' => json_decode(json_encode($response), true)
        ]);
    }

    public static function callApi(array $params = [])
    {
        $apiKey = config('services.pest.key');
        $endpoint = config('services.pest.endpoint');

        $query = http_build_query(array_merge(['apiKey' => $apiKey], $params));
        $url = "{$endpoint}?{$query}";

        $response = Http::get($url);
        $body = $response->body();

        if ($response->failed()) {
            throw new \Exception("API 호출 실패: {$response->status()}");
        }

        if (str_starts_with(trim($body), '{')) {
            $json = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON 파싱 실패: ' . json_last_error_msg());
            }
            return $json;
        }

        throw new \Exception("API 응답 형식을 인식할 수 없습니다: " . mb_substr($body, 0, 100));
    }

    public static function getSickKey(string $cropName, string $sickNameKor): ?string
    {
        $response = self::callApi([
            'serviceCode' => 'SVC01',
            'serviceType' => 'AA001:JSON',
            'cropName' => $cropName,
            'sickNameKor' => $sickNameKor,
        ]);

        if (
            !is_array($response['service']['list']) ||
            empty($response['service']['list'])
        ) {
            return null;
        }

        return $response['service']['list'][0]['sickKey'] ?? null;
    }

    public static function info(string $cropName, string $sickNameKor)
    {
        $sickKey = self::getSickKey($cropName, $sickNameKor);

        return self::callApi([
            'serviceCode' => 'SVC05',
            'sickKey' => $sickKey,
        ]);
    }
}
