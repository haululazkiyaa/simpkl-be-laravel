<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    use HasFactory;
    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'id_bimbingan',
        'tanggal',
        'jenis_pekerjaan',
        'deskripsi_pekerjaan',
        'bentuk_kegiatan',
        'jam_mulai',
        'jam_selesai',
        'staf',
        'foto',
        'catatan_pembimbing',
        'status',
        'createdAt',
        'updatedAt',
    ];

    public $timestamps = false;

    protected $table = 'jurnal_harian';
    public $incrementing = false;
    protected $keyType = 'string';

    public function kelompokBimbingan()
    {
        return $this->belongsTo(KelompokBimbingan::class, 'id_bimbingan');
    }
}
