<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $fillable = ['token', 'documents', 'type_document', 'paths', 'expires_at', 'created_at', 'used'];
    protected $table = "tokens";
    public $timestamps = false;

    public static function generateToken( $token, $path, $docType, $used = null ) {
        return self::create([
            'token' => $token,
            'paths' => $path,
            'type_document' => $docType,
            'expires_at' => now()->addMonth(),
            'created_at' => now()->format('Y-m-d'),
            'used' => $used,
        ]);
    }
    
}