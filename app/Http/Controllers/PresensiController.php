<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jurnal;
use App\Models\KelompokBimbingan;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    // @Fazlur
    public function getPresensiForPembimbing(Request $request)
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => null
        ];

        // Validate "tanggal" parameter
        $tanggal = $request->query('tanggal');
        if (!$tanggal) {
            $result['message'] = 'Parameter tanggal harus diisi...';
            return response()->json($result, 400);
        }

        $tanggal = date('Y-m-d', strtotime($tanggal));

        // Get Guru Pembimbing by NIP
        $guruPembimbing = Guru::where('nip', $request->usernameUser)->first();

        if (!$guruPembimbing) {
            $result['message'] = 'Guru Pembimbing tidak ditemukan';
            return response()->json($result, 404);
        }

        $idGuruPembimbing = $guruPembimbing->id;

        // Get Kelompok Bimbingan data
        $kelompokBimbingan = KelompokBimbingan::where('id_guru_pembimbing', $idGuruPembimbing)->get();

        if ($kelompokBimbingan->isEmpty()) {
            $result['message'] = 'Data Kelompok Bimbingan Tidak Ditemukan';
            return response()->json($result, 404);
        }

        // Prepare WHERE clause for Absensi
        $where = [
            'tanggal' => $tanggal
        ];

        if ($kelompokBimbingan->count() > 1) {
            $where['id_bimbingan'] = $kelompokBimbingan->pluck('id')->toArray();
        } else {
            $where['id_bimbingan'] = $kelompokBimbingan->first()->id;
        }

        // Search Absensi data
        $absensi = Absensi::with(['kelompok_bimbingan.siswa'])
            ->where($where)
            ->orderBy('tanggal', 'asc')
            ->get();

        if ($absensi->isNotEmpty()) {
            $result['success'] = true;
            $result['message'] = 'Data absensi berhasil ditampilkan...';
            $result['data'] = $absensi;
            return response()->json($result, 200);
        }

        // If no Absensi data, check Jurnal Harian
        $dataResponse = [];

        foreach ($kelompokBimbingan as $bimbingan) {
            $jurnal = Jurnal::where([
                'id_bimbingan' => $bimbingan->id,
                'tanggal' => $tanggal
            ])->first();

            $dataResponse[] = [
                'id_bimbingan' => $bimbingan->id,
                'tanggal' => $tanggal,
                'status' => $jurnal ? 'HADIR' : 'ALPA',
                'kelompok_bimbingan' => [
                    'id' => $bimbingan->id,
                    'siswa' => [
                        'id' => $bimbingan->siswa->id,
                        'nis' => $bimbingan->siswa->nis,
                        'nisn' => $bimbingan->siswa->nisn,
                        'nama' => $bimbingan->siswa->nama,
                    ]
                ]
            ];
        }

        $result['success'] = true;
        $result['message'] = 'Data absensi berhasil ditampilkan...';
        $result['data'] = $dataResponse;
        return response()->json($result, 200);
    }
    
}
