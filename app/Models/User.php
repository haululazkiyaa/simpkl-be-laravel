<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;
    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'username',
        'role',
        'message_token',
    ];

    public $timestamps = false;

    protected $table = 'user';
    public $incrementing = false;
    protected $keyType = 'string';
}
