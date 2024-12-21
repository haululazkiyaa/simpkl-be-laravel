<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;
    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'tahun_ajaran',
        'status',
        'createdAt',
        'updatedAt',
        'updatedBy',
        'createdBy'
    ];

    public $timestamps = false;

    protected $table = 'tahun_ajaran';
    public $incrementing = false;
    protected $keyType = 'string';
}
