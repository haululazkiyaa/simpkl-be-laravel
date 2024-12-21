<?php

namespace App\Http\Controllers;

use App\Helpers\BaseResponse;
use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Instruktur;
use App\Models\Jurnal;
use App\Models\Jurusan;
use App\Models\KelompokBimbingan;
use App\Models\Perusahaan;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KelompokBimbinganController extends Controller
{
    //Liat contoh API CRUD di JurusanController
    
    // Belva
    public function getAll()
    {
        try {
            $dataKelompokBimbingan = KelompokBimbingan::with([
                'siswa',
                'perusahaan',
                'guru_pembimbing',
                'instruktur'
            ])
            ->get();

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

    // Belva
    public function create(Request $request)
    {
        $response = new BaseResponse();

        try {
            $validator = Validator::make($request->all(), [
                'id_siswa' => 'required|uuid',
                'id_guru_pembimbing' => 'required|uuid',
                'id_perusahaan' => 'required|uuid',
                'id_instruktur' => 'nullable|uuid'
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
            $guruPembimbing = Guru::find($data['id_guru_pembimbing']);
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

            if ($perusahaan->status !== "Aktif") {
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
                'created_by' => $request->usernameUser,
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
    

    // Belva
    public function update(Request $request)
    {
        $response = new BaseResponse();
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|uuid',
                'id_siswa' => 'nullable|uuid',
                'id_guru_pembimbing' => 'nullable|uuid',
                'id_perusahaan' => 'nullable|uuid',
                'id_instruktur' => 'nullable|uuid',
                'status' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = $validator->errors()->first();
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();

            // Check existing Kelompok Bimbingan
            $kelompokBimbingan = KelompokBimbingan::find($data['id']);
            if (!$kelompokBimbingan) {
                $response->success = false;
                $response->message = "Data kelompok bimbingan tidak ditemukan...";
                return response()->json($response->toArray(), 404);
            }

            // Check Guru Pembimbing if provided
            if (!empty($data['id_guru_pembimbing'])) {
                $guruPembimbing = Guru::find($data['id_guru_pembimbing']);
                
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
            }

            // Check Perusahaan if provided
            if (!empty($data['id_perusahaan'])) {
                $perusahaan = Perusahaan::find($data['id_perusahaan']);
                
                if (!$perusahaan) {
                    $response->success = false;
                    $response->message = "Data perusahaan tidak terdaftar...";
                    return response()->json($response->toArray(), 400);
                }

                if ($perusahaan->status != "Aktif") {
                    $response->success = false;
                    $response->message = "Data perusahaan tidak aktif...";
                    return response()->json($response->toArray(), 400);
                }

                // Check if student already registered in this company
                $existingKelompok = KelompokBimbingan::where('id_siswa', $kelompokBimbingan->id_siswa)
                    ->where('id_perusahaan', $data['id_perusahaan'])
                    ->first();

                if ($existingKelompok && $existingKelompok->id != $data['id']) {
                    $response->success = false;
                    $response->message = "Siswa ini sudah pernah terdaftar pada perusahaan ini sebelumnya...";
                    return response()->json($response->toArray(), 400);
                }
            }

            // Check Instruktur if provided
            if (!empty($data['id_instruktur'])) {
                $instruktur = Instruktur::find($data['id_instruktur']);
                
                if (!$instruktur) {
                    $response->success = false;
                    $response->message = "Data instruktur tidak terdaftar...";
                    return response()->json($response->toArray(), 400);
                }

                $perusahaanId = $data['id_perusahaan'] ?? $kelompokBimbingan->id_perusahaan;

                if ($instruktur->id_perusahaan != $perusahaanId) {
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

            // Handle status update
            if (isset($data['status']) && $data['status'] === true) {
                $activeKelompok = KelompokBimbingan::where('id_siswa', $kelompokBimbingan->id_siswa)
                    ->where('status', true)
                    ->first();

                if ($activeKelompok) {
                    $activeKelompok->status = false;
                    $activeKelompok->updated_by = $request->usernameUser;
                    
                    if (!$activeKelompok->save()) {
                        DB::rollBack();
                        $response->success = false;
                        $response->message = "Internal Server Error";
                        return response()->json($response->toArray(), 500);
                    }
                }
            }

            // Update Kelompok Bimbingan
            $updateData = array_filter([
                'id_guru_pembimbing' => $data['id_guru_pembimbing'] ?? null,
                'id_perusahaan' => $data['id_perusahaan'] ?? null,
                'id_instruktur' => $data['id_instruktur'] ?? null,
                'status' => $data['status'] ?? null,
                'updated_by' => $request->usernameUser
            ]);
            
            $kelompokBimbingan->update($updateData);

            DB::commit();
            $response->success = true;
            $response->message = "Data Kelompok Bimbingan berhasil diubah...";
            $response->data = $kelompokBimbingan->fresh();

            return response()->json($response->toArray(), 200);

        } catch (\Exception $e) {
            DB::rollBack();
            $response->success = false;
            $response->message = "Internal Server Error";
            return response()->json($response->toArray(), 500);
        }
    }

    // Belva
    public function delete(Request $request)
    {
        $response = new BaseResponse();
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|uuid'
            ]);

            if ($validator->fails()) {
                $response->success = false;
                $response->message = $validator->errors()->first();
                return response()->json($response->toArray(), 400);
            }

            $data = $validator->validated();

            // Check if Kelompok Bimbingan exists
            $kelompokBimbingan = KelompokBimbingan::find($data['id']);
            if (!$kelompokBimbingan) {
                $response->success = false;
                $response->message = "Data Kelompok Bimbingan tidak ditemukan...";
                return response()->json($response->toArray(), 404);
            }

            // Check related Absensi
            $absensi = Absensi::where('id_bimbingan', $kelompokBimbingan->id)->first();
            
            // Check related Jurnal Harian
            $jurnalHarian = Jurnal::where('id_bimbingan', $kelompokBimbingan->id)->first();

            // Check if related data exists
            if ($absensi || $jurnalHarian) {
                $response->success = false;
                $response->message = "Data ini masih menyimpan data absensi atau jurnal harian siswa...";
                return response()->json($response->toArray(), 400);
            }

            // Delete Kelompok Bimbingan
            if ($kelompokBimbingan->delete()) {
                DB::commit();
                $response->success = true;
                $response->message = "Data kelompok bimbingan berhasil dihapus...";
                return response()->json($response->toArray(), 200);
            } else {
                DB::rollBack();
                $response->success = false;
                $response->message = "Internal Server Error";
                return response()->json($response->toArray(), 500);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $response->success = false;
            $response->message = "Internal Server Error";
            return response()->json($response->toArray(), 500);
        }
    }

    
}
