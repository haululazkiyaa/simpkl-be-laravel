<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instruktur extends Model
{
    use HasFactory;
    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'id_perusahaan',
        'username',
        'nama',
        'no_hp',
        'status_aktif',
        'updatedAt',
        'updatedBy',
        'createdBy'
    ];

    public $timestamps = false;

    protected $table = 'instruktur';
    public $incrementing = false;
    protected $keyType = 'string';
}
