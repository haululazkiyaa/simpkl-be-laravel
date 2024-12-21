<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;
    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'nip',
        'nama',
        'alamat',
        'no_hp',
        'status_aktif',
        'createdAt',
        'updatedAt',
    ];

    public $timestamps = false;

    protected $table = 'guru_pembimbing';
    public $incrementing = false;
    protected $keyType = 'string';
}
