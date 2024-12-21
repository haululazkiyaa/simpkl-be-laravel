<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;
    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'id_jurusan',
        'nis',
        'nisn',
        'nama',
        'alamat',
        'no_hp',
        'tempat_lahir',
        'tanggal_lahir',
        'status_aktif',
        'createdAt',
        'updatedAt',
    ];

    public $timestamps = false;

    protected $table = 'siswa';
    public $incrementing = false;
    protected $keyType = 'string';
}
