<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;
    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'username',
        'nama_perusahaan',
        'pimpinan',
        'alamat',
        'no_hp',
        'email',
        'website',
        'createdAt',
        'updatedAt',
        'status',
    ];

    public $timestamps = false;

    protected $table = 'perusahaan';
    public $incrementing = false;
    protected $keyType = 'string';
}
