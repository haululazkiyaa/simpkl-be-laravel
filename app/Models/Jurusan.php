<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;
    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'bidang_keahlian',
        'program_keahlian',
        'kompetensi_keahlian',
        'createdAt',
        'updatedAt',
        'updatedBy',
        'createdBy'
    ];

    public $timestamps = false;

    protected $table = 'jurusan';
    public $incrementing = false;
    protected $keyType = 'string';
}
