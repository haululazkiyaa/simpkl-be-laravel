<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Jurusan;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JurusanController extends Controller
{
    public function index()
    {
        try {
            $dataJurusan = Jurusan::all();

            $response = new BaseResponse(
                success: true,
                message: "Data jurusan berhasil ditampilkan",
                data: $dataJurusan
            );
            return response()->json($response->toArray(), 200);
        } catch (Exception $e) {
            $response = new BaseResponse(
                success: false,
                message: "Internal Server Error",
                data: null
            );
            return response()->json($response->toArray(), 500);
        }
    }

    public function store(Request $request)
    {
        $response = new BaseResponse();
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'bidang_keahlian' => 'required|string|max:255',
                'program_keahlian' => 'required|string|max:255',
                'kompetensi_keahlian' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = 'there was something wrong with his request';
                $response->data = null;
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();
            $jurusan = Jurusan::create($data);

            DB::commit();
            $response->success = true;
            $response->message = "Jurusan berhasil ditambahkan";
            $response->data = $jurusan;

            return response()->json($response->toArray(), 201);
        } catch (Exception $e) {
            DB::rollBack();

            $response->success = false;
            $response->message = "Internal Server Error";
            return response()->json($response->toArray(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $jurusan = Jurusan::find($id);

            if (!$jurusan) {
                return response()->json([
                    'success' => false,
                    'message' => "Data jurusan tidak ditemukan",
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => "Data jurusan berhasil ditampilkan",
                'data' => $jurusan,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $response = new BaseResponse();

        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required|uuid',
                'bidang_keahlian' => 'required|string|max:255',
                'program_keahlian' => 'required|string|max:255',
                'kompetensi_keahlian' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = 'there was something wrong with his request';
                $response->data = null;
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();
            $jurusan = Jurusan::find($data['id']);

            if (!$jurusan) {
                return response()->json([
                    'success' => false,
                    'message' => "Data jurusan tidak ditemukan",
                ], 400);
            }
            
            $jurusan->update([
                'bidang_keahlian' => $data['bidang_keahlian'],
                'program_keahlian' => $data['program_keahlian'],
                'kompetensi_keahlian' => $data['kompetensi_keahlian'],
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Data jurusan berhasil diperbarui",
                'data' => $jurusan,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $response = new BaseResponse();
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|uuid'
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = 'there was something wrong with his request';
                $response->data = null;
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();
            $jurusan = Jurusan::find($data['id']);

            if (!$jurusan) {
                return response()->json([
                    'success' => false,
                    'message' => "Data jurusan tidak ditemukan",
                ], 400);
            }

            $jurusan->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Data jurusan berhasil dihapus",
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => "Internal Server Error",
            ], 500);
        }
    }
}
