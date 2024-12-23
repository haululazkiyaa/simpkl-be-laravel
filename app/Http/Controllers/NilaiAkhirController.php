<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Validator;

class NilaiAkhirController extends Controller
{
    private $nodejsUrl;

    public function __construct()
    {
        $this->nodejsUrl = config('app.nodejs_api_url');
    }

    public function getPembimbing(Request $request)
    {
        try {
            $idSiswa = $request->query('id_siswa');
            $response = Http::withHeaders([
                'Authorization' => $request->header('Authorization'),
            ])->get("{$this->nodejsUrl}/nilai-akhir?id_siswa=$idSiswa");

            return $response->json();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSiswa(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $request->header('Authorization'),
            ])->get("{$this->nodejsUrl}/nilai-akhir/siswa");
            return $response->json();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function gradePembimbing(Request $request)
    {
        $response = new BaseResponse();

        try {
            $validator = Validator::make($request->all(), [
                'id_siswa' => 'required|uuid',
                'data' => 'required|array',
                'data.*.id_aspek_penilaian' => 'required|uuid',
                'data.*.nilai' => 'required|integer|min:0|max:100',
                'data.*.keterangan' => 'nullable|string'
            ]);


            if ($validator->fails()) {
                $response->success = false;
                $response->message = 'there was something wrong with his request';
                $response->data = null;
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();

            $response = Http::withHeaders([
                'Authorization' => $request->header('Authorization'),
            ])->post("{$this->nodejsUrl}/nilai-akhir/create", [
                'id_siswa' => $data['id_siswa'],
                'data' => $data['data']
            ]);

            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();
            return response()->json($response->toArray(), 500);
        }
    }
}
