<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Validator;

class AspekPenilaianController extends Controller
{
    private $nodejsUrl;

    public function __construct()
    {
        $this->nodejsUrl = config('app.nodejs_api_url');
    }

    public function index()
    {
        try {
            $response = Http::get("{$this->nodejsUrl}/aspek-penilaian/all");
            return $response->json();

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request){
        $response = new BaseResponse();

        try {
            $validator = Validator::make($request->all(), [
                'judul' => 'required|string',
                'kelompok_penilaian' => 'required|string'
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = 'there was something wrong with his request';
                $response->data = null;
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();
            
            $response = Http::post("{$this->nodejsUrl}/aspek-penilaian/create", [
                'judul' => $data['judul'],
                'kelompok_penilaian' => $data['kelompok_penilaian']
            ]);

            return response()->json($response->json(), $response->status());

        } catch (Exception $e) {
            $response->success = false;
            $response->message = "Internal Server Error";
            return response()->json($response->toArray(), 500);
        }
    }

    public function update(Request $request){
        $response = new BaseResponse();

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|uuid',
                'judul' => 'required|string',
                'kelompok_penilaian' => 'required|string'
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = 'there was something wrong with his request';
                $response->data = null;
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();
            
            $response = Http::put("{$this->nodejsUrl}/aspek-penilaian/update", [
                'id' => $data['id'],
                'judul' => $data['judul'],
                'kelompok_penilaian' => $data['kelompok_penilaian']
            ]);

            return response()->json($response->json(), $response->status());

        } catch (Exception $e) {
            $response->success = false;
            $response->message = "Internal Server Error";
            return response()->json($response->toArray(), 500);
        }
    }
}
