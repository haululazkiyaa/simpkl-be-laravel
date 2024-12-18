<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JurnalHarianController extends Controller
{

    // @Shodiq
    public function getForPembimbing()
    {
        //Mengambil Seluruh Jurnal harian bimbingannya
        // logika nya:
        // 1. Get data guru pembimbing where nip = request.usernameUser dari hasil setelah login
        // 2. Get seluruh data kelompok bimbingan where id_guru_pembimbing sama dengan id guru setelah dapat data guru pada langkah no 1
        // 3. setelah itu ambil semua data jurnal harian where(where nya berupa array) id_kelompok_bimbingan dari hasil yang didapat di langkah no 2 AND tanggal = request query tanggalnya
        // 4. Sesuaikan hasil response dengan api yang udah ada sebelumnya.
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
    }

    // @Shodiq
    public function setStatus(Request $request)
    {
        // logikanya:
        // 1. sama seperti addCatatanPembimbing, tapi ini update statusnya
    }
}
