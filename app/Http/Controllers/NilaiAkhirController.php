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

    public function pembimbing(Request $request)
    {
        try {
            $idSiswa = $request->query('id_siswa');
            $response = Http::get("{$this->nodejsUrl}/nilai-akhir", [
                'query' => [
                    'id_siswa' => $idSiswa
                ]
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

    public function siswa()
    {
        try {
            $response = Http::get("{$this->nodejsUrl}/nilai-akhir/siswa");
            return $response->json();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        $response = new BaseResponse();

        try {
            $validator = Validator::make($request->all(), [
                'judul' => 'required|string',
                'kelompok_penilaian' => 'required|string',
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

            $response = Http::post("{$this->nodejsUrl}/nilai-akhir/create", [
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
