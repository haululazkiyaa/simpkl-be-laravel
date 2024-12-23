<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;
    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'id_bimbingan',
        'tanggal',
        'status',
        'createdAt',
        'updatedAt',
    ];

    public $timestamps = false;

    protected $table = 'absensi';
    public $incrementing = false;
    protected $keyType = 'string';

    public function kelompok_bimbingan()
    {
        return $this->belongsTo(KelompokBimbingan::class, 'id_bimbingan');
    }
}
