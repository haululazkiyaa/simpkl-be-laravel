<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TahunAjaranController extends Controller
{
    private $nodejsUrl;

    public function __construct()
    {
        $this->nodejsUrl = config('app.nodejs_api_url');
    }

    public function index(Request $request)
    {
        try {
            $authHeader = $request->header('Authorization');

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return response()->json([
                    'success' => false,
                    'message' => "Unauthorized"
                ], 401);
            }

            $response = Http::withHeaders([
                'Authorization' => $authHeader,
            ])->get("{$this->nodejsUrl}/tahun-ajaran/all");

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
            $authHeader = $request->header('Authorization');

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return response()->json([
                    'success' => false,
                    'message' => "Unauthorized"
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'tahun_ajaran' => 'required|string|max:100'
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = 'there was something wrong with his request';
                $response->data = null;
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();
            
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
            ])->post("{$this->nodejsUrl}/tahun-ajaran/create", [
                'tahun_ajaran' => $data['tahun_ajaran']
            ]);

            return $response->json();

        } catch (Exception $e) {
            $response->success = false;
            $response->message = "Internal Server Error";
            return response()->json($response->toArray(), 500);
        }
    }

    public function updateStatus(Request $request){
        $response = new BaseResponse();

        try {
            $authHeader = $request->header('Authorization');

            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return response()->json([
                    'success' => false,
                    'message' => "Unauthorized"
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'id' => 'required|uuid',
                'status' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = 'there was something wrong with his request';
                $response->data = null;
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();
            
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
            ])->put("{$this->nodejsUrl}/tahun-ajaran/status", [
                'id' => $data['id'],
                'status' => $data['status']
            ]);

            return response()->json($response->json(), $response->status());

        } catch (Exception $e) {
            $response->success = false;
            $response->message = "Internal Server Error";
            return response()->json($response->toArray(), 500);
        }
    }
}
