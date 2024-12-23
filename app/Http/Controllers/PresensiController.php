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
        $tanggal = $request->query('tanggal');
        if (!$tanggal) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal harus disertakan dalam permintaan.'
            ], 400);
        }

        $nip = $request->usernameUser; 
        $guru = Guru::where('nip', $nip)->first();
    
        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'Data guru pembimbing tidak ditemukan.'
            ], 404); 
        }
        
        $kelompokBimbingan = KelompokBimbingan::where('id_guru_pembimbing', $guru->id)->get();
        
        if ($kelompokBimbingan->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data kelompok bimbingan tidak ditemukan.'
            ], 404); 
        }
        
        $idKelompok = KelompokBimbingan::where('id_guru_pembimbing', $guru->id)
                ->pluck('id'); 

                return $idKelompok;
        $presensi = Absensi::whereIn('id_bimbingan', $idKelompok)
                            ->where('tanggal', $tanggal)
                            ->get();
    
        if ($presensi->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data presensi berhasil ditemukan.',
                'data' => $presensi
            ]);
        }
    
        $hasilPresensi = [];
        foreach ($kelompokBimbingan as $kelompok) {
            $jurnal = Jurnal::where('id_bimbingan', $kelompok->id)
                                  ->where('tanggal', $tanggal)
                                  ->first();
    
            $hasilPresensi[] = [
                'id_kelompok_bimbingan' => $kelompok->id,
                'nama_kelompok' => $kelompok->nama_kelompok,
                'status' => $jurnal ? 'HADIR' : 'ALPA'
            ];
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Data presensi dihitung berdasarkan jurnal harian.',
            'data' => $hasilPresensi
        ]);
    }
    
}
