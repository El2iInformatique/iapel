<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuntimeSecret extends Model
{
    protected $connection = 'runtime_secrets';

    protected $fillable = [
        'key',
        'value',
        'updated_by',
    ];
}
