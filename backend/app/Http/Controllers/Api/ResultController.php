<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Train;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResultController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if(!$user){
            return response()->json(['message'=>'Unauthorized'], 401);
        }

        $results = $user->trains()->latest()->paginate(6);

        return response()->json($results);
    }

    public function show($id)
    {

        $user = auth()->user();

        $result = $user->trains()->find($id);

        if (!$result) {
            return response()->json(['error'=>'해당 결과를 찾을 수 없거나 권한이 없습니다'], 404);
        }
        return response()->json($result);
    }

    public function destroy($id){

        $user = auth()->user();
        $photo = $user->trains()->find($id);

        if(!$photo)
        {
            return response()->json(['error' => '사진을 찾을 수 없거나 권한이 없습니다'], 404);
        }

        if(Storage::disk('public')->exists('images/' . $photo->url)) {
            Storage::disk('public')->delete('images/' . $photo->url);
        }
        //DB delete
        $photo->delete();

        return response()->json(['message' => '삭제 완료'], 200);
    }
}
