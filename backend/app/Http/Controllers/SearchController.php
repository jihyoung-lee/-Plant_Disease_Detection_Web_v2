<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SearchController extends Controller
{
    public function infoApi(Request $request)
    {
        $cropName = $request->input('cropName');
        $sickNameKor = $request->input('sickNameKor');

        try {
            $xml = $this->info($cropName, $sickNameKor);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'API 호출 실패',
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'cropName' => $cropName,
            'sickNameKor' => $sickNameKor,
            'raw' => json_decode(json_encode($xml), true)
        ]);
    }

    public function getSickKeyApi(Request $request)
    {
        $cropName = $request->input('cropName');
        $sickNameKor = $request->input('sickNameKor');

        try {
            $sickKey = $this->getSickKey($cropName, $sickNameKor);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'SickKey 조회 실패',
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json(['sickKey' => $sickKey]);
    }

    protected function callApi(array $params = [])
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

        // JSON 형태로 시작하면 json_decode 처리
        if (str_starts_with(trim($body), '{')) {
            $json = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON 파싱 실패: ' . json_last_error_msg());
            }
            return $json;
        }

        // XML 형태인 경우
        if (str_starts_with(trim($body), '<')) {
            return simplexml_load_string($body) ?: throw new \Exception('XML 파싱 실패');
        }

        throw new \Exception("API 응답 형식을 인식할 수 없습니다: " . mb_substr($body, 0, 100));
    }

    protected function getSickKey(string $cropName, string $sickNameKor): ?string
    {
        $response = $this->callApi([
            'serviceCode' => 'SVC01',
            'serviceType' => 'AA001:JSON',
            'cropName' => $cropName,
            'sickNameKor' => $sickNameKor,
        ]);

        if (
            !isset($response['service']['list']) ||
            !is_array($response['service']['list']) ||
            empty($response['service']['list'])
        ) {
            return null; // sickKey 못 찾음
        }

        return $response['service']['list'][0]['sickKey'] ?? null;
    }

    public function info($cropName, $sickNameKor)
    {
        $sickKey = $this->getSickKey($cropName, $sickNameKor);

        $xml = $this->callApi([
            'serviceCode' => 'SVC05',
            'sickKey' => $sickKey,
        ]);

        return $xml;
    }
}
