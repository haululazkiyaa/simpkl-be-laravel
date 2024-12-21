<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Siswa;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KelompokBimbingan extends Model
{
    use HasFactory;
    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'id_siswa',
        'id_guru_pembimbing',
        'id_perusahaan',
        'id_tahun_ajaran',
        'status',
        'createdAt',
        'updatedAt',
    ];

    public $timestamps = false;

    protected $table = 'kelompok_bimbingan';
    public $incrementing = false;
    protected $keyType = 'string';

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru_pembimbing');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }
}
