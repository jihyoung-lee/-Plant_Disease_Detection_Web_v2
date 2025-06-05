<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Train;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        $results = Train::latest()->paginate(5);
        return response()->json($results);
    }

    public function show($id)
    {
        $result = Train::findOrFail($id);
        return response()->json($result);
    }
}
