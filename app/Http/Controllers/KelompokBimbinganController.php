<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Jurusan;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KelompokBimbinganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $dataKelompokBimbingan = Jurusan::all();

            $response = new BaseResponse(
                success: true,
                message: "Data Kelompok Bimbingan berhasil ditampilkan",
                data: $dataKelompokBimbingan
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $response = new BaseResponse();

        try {
            $validator = Validator::make($request->all(), [
                'id_siswa' => 'required|string',
                'id_guru_pembimbing' => 'required|string',
                'id_perusahaan' => 'required|string',
                'id_instruktur' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = $validator->errors()->first();
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();

            // Check Siswa
            $siswa = Siswa::find($data['id_siswa']);
            if (!$siswa) {
                $response->success = false;
                $response->message = "Data siswa tidak terdaftar...";
                return response()->json($response->toArray(), 400);
            }

            if (!$siswa->status_aktif) {
                $response->success = false;
                $response->message = "Data siswa sudah tidak aktif...";
                return response()->json($response->toArray(), 400);
            }

            // Check Guru Pembimbing
            $guruPembimbing = GuruPembimbing::find($data['id_guru_pembimbing']);
            if (!$guruPembimbing) {
                $response->success = false;
                $response->message = "Data guru pembimbing tidak terdaftar...";
                return response()->json($response->toArray(), 400);
            }

            if (!$guruPembimbing->status_aktif) {
                $response->success = false;
                $response->message = "Data guru pembimbing sudah tidak aktif...";
                return response()->json($response->toArray(), 400);
            }

            // Check Perusahaan
            $perusahaan = Perusahaan::find($data['id_perusahaan']);
            if (!$perusahaan) {
                $response->success = false;
                $response->message = "Data perusahaan tidak terdaftar...";
                return response()->json($response->toArray(), 400);
            }

            if ($perusahaan->status !== Constants::AKTIF) {
                $response->success = false;
                $response->message = "Data perusahaan tidak aktif...";
                return response()->json($response->toArray(), 400);
            }

            // Check existing Kelompok Bimbingan
            $existingKelompokBimbingan = KelompokBimbingan::where('id_siswa', $data['id_siswa'])
                ->where('id_perusahaan', $data['id_perusahaan'])
                ->first();

            if ($existingKelompokBimbingan) {
                $response->success = false;
                $response->message = "Data siswa sudah terdaftar sebelumnya pada perusahaan yang dipilih...";
                return response()->json($response->toArray(), 400);
            }

            $status = true;
            $checkAnotherKelompokBimbingan = KelompokBimbingan::where('id_siswa', $data['id_siswa'])->first();
            if ($checkAnotherKelompokBimbingan) {
                $status = false;
            }

            // Check Instruktur if provided
            if (!empty($data['id_instruktur'])) {
                $instruktur = Instruktur::find($data['id_instruktur']);
                
                if (!$instruktur) {
                    $response->success = false;
                    $response->message = "Data instruktur tidak terdaftar...";
                    return response()->json($response->toArray(), 400);
                }

                if ($instruktur->id_perusahaan != $data['id_perusahaan']) {
                    $response->success = false;
                    $response->message = "Data instruktur tidak terdaftar pada perusahaan yang dipilih...";
                    return response()->json($response->toArray(), 400);
                }

                if (!$instruktur->status_aktif) {
                    $response->success = false;
                    $response->message = "Data instruktur sudah tidak aktif...";
                    return response()->json($response->toArray(), 400);
                }
            }

            // Find active Tahun Ajaran
            $tahunAjaran = TahunAjaran::where('status', true)->first();
            if (!$tahunAjaran) {
                $response->success = false;
                $response->message = "Internal Server Error...";
                return response()->json($response->toArray(), 500);
            }

            // Create new Kelompok Bimbingan
            $newKelompokBimbingan = KelompokBimbingan::create([
                'id_siswa' => $data['id_siswa'],
                'id_guru_pembimbing' => $data['id_guru_pembimbing'],
                'id_perusahaan' => $data['id_perusahaan'],
                'id_instruktur' => $data['id_instruktur'] ?? null,
                'id_tahun_ajaran' => $tahunAjaran->id,
                'status' => $status,
                'created_by' => auth()->user()->username // Assuming you have authentication
            ]);

            $response->success = true;
            $response->message = "Kelompok bimbingan berhasil ditambahkan...";
            $response->data = $newKelompokBimbingan;

            return response()->json($response->toArray());
        }
        catch (\Exception $e) {
            $response->success = false;
            $response->message = "Internal Server Error";
            return response()->json($response->toArray(), 500);
        }
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
