<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'scholar_id',
        'name',
        'manager_ronin',
        'rate',
        'access_token',
        'private_key'
    ];
}
