<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jurnal;
use App\Models\KelompokBimbingan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        $absensi = Absensi::with(['kelompok_bimbingan.siswa'])
            ->where('tanggal', $tanggal)
            ->whereIn('id_bimbingan', $kelompokBimbingan->pluck('id')->toArray())
            ->orderBy('tanggal', 'asc')->get();

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

    public function storeAbsensiPembimbing(Request $request)
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => null
        ];

        // Validation Schema
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'data' => 'required|array',
            'data.*.id_bimbingan' => 'required|string',
            'data.*.status' => 'required|string|in:Hadir,Libur,Sakit,Alpha,Izin'
        ]);

        if ($validator->fails()) {
            $result['message'] = $validator->errors()->first();
            $result['data'] = $validator->errors();
            return response()->json($result, 400);
        }

        $tanggal = $request->input('tanggal');
        $data = $request->input('data');

        // Check Guru Pembimbing
        $guruPembimbing = Guru::where('nip', $request->usernameUser)->first();

        if (!$guruPembimbing) {
            $result['message'] = 'Guru Pembimbing tidak ditemukan';
            return response()->json($result, 404);
        }

        $userId = $guruPembimbing->id;

        // Validate each bimbingan
        foreach ($data as $element) {
            $kelompokBimbingan = KelompokBimbingan::find($element['id_bimbingan']);

            if (!$kelompokBimbingan) {
                $result['message'] = 'Salah satu data bimbingan tidak ditemukan...';
                return response()->json($result, 404);
            }

            if ($kelompokBimbingan->id_guru_pembimbing !== $userId) {
                $result['message'] = 'Anda tidak bisa mengakses salah satu data bimbingan...';
                return response()->json($result, 403);
            }
        }

        // Bulk insert absensi
        $absensiData = [];
        foreach ($data as $element) {
            $absensi = Absensi::where('tanggal', $tanggal)
                ->where('id_bimbingan', $element['id_bimbingan'])
                ->first();

            if ($absensi) {
                // If absensi exists, update the status
                $absensi->update([
                    'status' => $element['status'],
                    'updated_at' => now(),
                ]);
                $absensiData[] = $absensi; // Store updated absensi data
            } else {
                // If absensi does not exist, create a new record
                $absensiData[] = Absensi::create([
                    'tanggal' => $tanggal,
                    'id_bimbingan' => $element['id_bimbingan'],
                    'status' => $element['status'],
                ]);
            }
        }

        if ($absensiData) {
            $result['success'] = true;
            $result['message'] = 'Absensi bimbingan berhasil disimpan...';
            $result['data'] = $absensiData;
            return response()->json($result, 201);
        } else {
            $result['message'] = 'Internal Server Error';
            return response()->json($result, 500);
        }
    }
}
