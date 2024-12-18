<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PerusahaanController extends Controller
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

            $response = Http::withHeaders([
                'Authorization' => $authHeader,
            ])->get("{$this->nodejsUrl}/perusahaan/all");

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
        try {
            $authHeader = $request->header('Authorization');

            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'nama_perusahaan' => 'required|string',
                'pimpinan' => 'required|string',
                'alamat' => 'required|string',
                'no_hp' => 'required|string',
                'email' => 'nullable|string|email',
                'website' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'there was something wrong with his request',
                ], 400);
            }

            $data = $validator->validated();
            
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
            ])->post("{$this->nodejsUrl}/perusahaan/create", [
                'username' => $data['username'],
                'nama_perusahaan' => $data['nama_perusahaan'],
                'pimpinan' => $data['pimpinan'],
                'alamat' => $data['alamat'],
                'no_hp' => $data['no_hp'],
                'email' => $data['email'] ?? null,
                'website' => $data['website'] ?? null
            ]);

            return $response->json();

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request){

        try {
            $authHeader = $request->header('Authorization');

            $validator = Validator::make($request->all(), [
                'id' => 'required|uuid',
                'username' => 'nullable|string',
                'nama_perusahaan' => 'nullable|string',
                'pimpinan' => 'nullable|string',
                'alamat' => 'nullable|string',
                'no_hp' => 'nullable|string',
                'email' => 'nullable|string|email',
                'website' => 'nullable|string',
                'status' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'there was something wrong with his request',
                ], 400);
            }

            $data = $validator->validated();
            
            $response = Http::withHeaders([
                'Authorization' => $authHeader,
            ])->put("{$this->nodejsUrl}/perusahaan/update", [
                'id' => $data['id'] ?? null,
                'username' => $data['username'] ?? null,
                'nama_perusahaan' => $data['nama_perusahaan'] ?? null,
                'pimpinan' => $data['pimpinan'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'email' => $data['email'] ?? null,
                'website' => $data['website'] ?? null
            ]);

            return $response->json();

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
