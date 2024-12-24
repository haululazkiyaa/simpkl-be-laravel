<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jurnal;
use App\Models\KelompokBimbingan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Exception;

class JurnalHarianController extends Controller
{
    private $nodejsUrl;

    public function __construct()
    {
        $this->nodejsUrl = config('app.nodejs_api_url');
    }

    // @Shodiq
    //Mengambil Seluruh Jurnal harian bimbingannya
    // logika nya:
    // 1. Get data guru pembimbing where nip = request.usernameUser dari hasil setelah login
    // 2. Get seluruh data kelompok bimbingan where id_guru_pembimbing sama dengan id guru setelah dapat data guru pada langkah no 1
    // 3. setelah itu ambil semua data jurnal harian where(where nya berupa array) id_kelompok_bimbingan dari hasil yang didapat di langkah no 2 AND tanggal = request query tanggalnya
    // 4. Sesuaikan hasil response dengan api yang udah ada sebelumnya.
    public function getForPembimbing(Request $request)
    {
        try {
            // 1. Get data guru pembimbing berdasarkan NIP dari request (usernameUser)
            $guruPembimbing = Guru::where('nip', $request->usernameUser)->first();
            
            if (!$guruPembimbing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru pembimbing tidak ditemukan.',
                ], 400);
            }
    
            // 2. Get semua data kelompok bimbingan di mana id_guru_pembimbing sama dengan id guru yang didapatkan
            $kelompokBimbinganIds = KelompokBimbingan::where('id_guru_pembimbing', $guruPembimbing->id)
                // ->where('status', true)
                ->pluck('id'); // Hanya mengambil ID kelompok bimbingan
    
            if ($kelompokBimbinganIds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok bimbingan tidak ditemukan.',
                ], 400);
            }
    
            // 3. Get semua jurnal harian berdasarkan id_kelompok_bimbingan dan tanggal dari query
            $tanggal = $request->query('tanggal');
            if(!$tanggal){
                return response()->json([
                    'success' => false,
                    'message' => 'Tanggal jurnal harus diisi.',
                ], 400);
            }
            $jurnalHarian = Jurnal::whereIn('id_bimbingan', $kelompokBimbinganIds)
                ->where('tanggal', $tanggal)
                ->with([
                    'kelompokBimbingan.siswa',
                    'kelompokBimbingan.perusahaan'
                ])
                ->get();

    
            if ($jurnalHarian->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data jurnal harian tidak ditemukan.',
                ], 400);
            }
    
            // 4. Sesuaikan response dengan format API sebelumnya
            return response()->json([
                'success' => true,
                'data' => $jurnalHarian
            ], 200);
        } catch (\Exception $e) {
            // Handle error jika terjadi
            return response()->json([
                'success' => false,
                'message' => 'Internal server error.',
            ], 500);
        }
    }

    // @Shodiq
    public function addCatatanPembimbing(Request $request)
    {
        // logikanya:
        // 1. cari jurnal berdasarkan id yang diinputkan
        // 2. Kemudian get data kelompok bimbingan berdasarkan id_kelompok_bimbingan dari jurnal tersebut
        // 3. cari data guru pembimbing berdasarkan nip = request.usernameUser
        // 4. Setelah itu bandingkan apakah id guru pada step no 2 sama dengan step nomor 3.
        // 5. kalau tidak bisa jangan berikan akses untuk edit data. LIAT CONTOH RESPONSE DI API LAMA
        // 6. kalau sukses, update data catatan_pembimbing di jurnalnya
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'id' => 'required|uuid',
                'catatan' => 'required|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'there was something wrong with his request.',
                ], 400);
            }
            
            $data = $validator->validated();
            
            // 1. Cari jurnal berdasarkan id yang diinputkan
            $jurnal = Jurnal::where("id", $data['id'])->first();
            
            if (!$jurnal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jurnal tidak ditemukan.',
                ], 400);
            }
    
            // 2. Get data kelompok bimbingan berdasarkan id_kelompok_bimbingan dari jurnal tersebut
            $kelompokBimbingan = KelompokBimbingan::where('id', $jurnal->id_bimbingan)
                ->first();

            if (!$kelompokBimbingan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok bimbingan tidak ditemukan.',
                ], 400);
            }
    
            // 3. Cari data guru pembimbing berdasarkan nip = request.usernameUser
            $guru = Guru::where('nip', $request->usernameUser)->first();
    
            if (!$guru) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru pembimbing tidak ditemukan.',
                ], 400);
            }
    
            // 4. Bandingkan apakah id guru pada step no 2 sama dengan step nomor 3
            if ($kelompokBimbingan->id_guru_pembimbing !== $guru->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengedit data ini.',
                ], 400);
            }
    
            // 5. Jika validasi berhasil, update data catatan_pembimbing di jurnalnya
            $jurnal->update([
                'catatan_pembimbing' => $data['catatan']
            ]);

            $dataUser = User::join('siswa', 'siswa.nisn', '=', 'user.username')
                ->where('siswa.id', $kelompokBimbingan->id_siswa)
                ->first();

            if ($dataUser && $dataUser->message_token) {
                // Kirim notifikasi
                Http::post("{$this->nodejsUrl}/send-notification", [
                    'token' => $dataUser->message_token,
                    'title' => "Catatan Pembimbing",
                    'body' => $data['catatan']
                ]);
            }
    
            // 6. Response sukses
            return response()->json([
                'success' => true,
                'message' => 'Catatan pembimbing berhasil ditambahkan.',
                'data' => $jurnal,
            ], 200);
    
        } catch (Exception $e) {
            // Handle error jika terjadi
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // @Shodiq
    public function setStatus(Request $request)
    {
        // logikanya:
        // 1. sama seperti addCatatanPembimbing, tapi ini update statusnya
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'id_jurnal' => 'required|uuid',
                'status' => 'required|string|in:Diterima,Ditolak'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'there was something wrong with his request.',
                    'error' => $validator->errors()
                ], 400);
            }
            
            $data = $validator->validated();
    
            // 1. Cari jurnal berdasarkan id yang diinputkan
            $jurnal = Jurnal::find($request->id_jurnal);
    
            if (!$jurnal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jurnal tidak ditemukan.',
                ], 400);
            }
    
            // 2. Get data kelompok bimbingan berdasarkan id_kelompok_bimbingan dari jurnal tersebut
            $kelompokBimbingan = $jurnal->kelompokBimbingan;
    
            if (!$kelompokBimbingan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok bimbingan tidak ditemukan.',
                ], 400);
            }
    
            // 3. Cari data guru pembimbing berdasarkan nip = request.usernameUser
            $guru = Guru::where('nip', $request->usernameUser)->first();
    
            if (!$guru) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru pembimbing tidak ditemukan.',
                ], 400);
            }
    
            // 4. Bandingkan apakah id guru pada step no 2 sama dengan step nomor 3
            if ($kelompokBimbingan->id_guru_pembimbing !== $guru->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengubah status jurnal ini.',
                ], 400);
            }
    
            // 5. Jika validasi berhasil, update data status di jurnalnya
            $jurnal->update([
                'status' => $request->status,
            ]);

            $dataUser = User::join('siswa', 'siswa.nisn', '=', 'user.username')
                ->where('siswa.id', $kelompokBimbingan->id_siswa)
                ->first();

            if ($dataUser && $dataUser->message_token) {
                // Kirim notifikasi
                if($request->status == "Diterima"){
                    $title = "Jurnal Diterima";
                    $body = "Selamat, Jurnal Anda Diterima oleh Pembimbing";
                }else{
                    $title = "Jurnal Ditolak";
                    $body = "Yaah, Jurnal Anda Ditolak oleh Pembimbing :(";
                }

                Http::post("{$this->nodejsUrl}/send-notification", [
                    'token' => $dataUser->message_token,
                    'title' => $title,
                    'body' => $body
                ]);
            }
    
            // 6. Response sukses
            return response()->json([
                'success' => true,
                'message' => 'Status jurnal berhasil diperbarui.',
                'data' => $jurnal,
            ], 200);
    
        } catch (\Exception $e) {
            // Handle error jika terjadi
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
