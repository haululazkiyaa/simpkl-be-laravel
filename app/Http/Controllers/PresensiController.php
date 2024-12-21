<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PresensiController extends Controller
{
    // @Fazlur
    public function getPresensiForPembimbing(Request $request)
    {
        // logikanya:
        // 1. cek dulu apakah ada req.query tanggal
        // 2. kalau tidak berarti gagal. (CEK CONOTH RESPONSE DI API LAMA)
        // 3. Ambil data guru pembimbing berdasarkan nip = request.usernameUser
        // 4. Get seluruh data kelompok bimbingan where id_guru_pembimbing sama dengan id guru setelah dapat data guru pada langkah no 3
        // 5. Jika data presensi udah ada di DB, maka:
        // 6. ambil semua data presensi where(where nya berupa array) id_kelompok_bimbingan dari hasil yang didapat di langkah no 2 AND tanggal = request query tanggalnya
        // 7. kalau data presensinya blm ada di DB, maka:
        // 8. lakukan perulangan dari data kelompok bimbingan pada step no 4 untuk mengambil data jurnal harian where id_bimbingan : kelompokbimbingan[i].id && tanggal
        // 9. dalam perulangan tersebut, kalau data tidak ditemukan, berarti "ALPA" kala ditemukan berarti "HADIR"
        // 10. cek contoh response pada api yang lama
    }
}
