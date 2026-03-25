<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenLinks extends Model
{
    use HasFactory;

    protected $fillable = ['token', 'documents', 'paths', 'expires_at', 'created_at'];
    protected $table = "token_links";
    public $timestamps = false;

    public static function generateToken( $token, $path, string $documents = "" ) {
        return self::create([
            'token' => $token,
            'documents' => $documents,
            'paths' => $path,
            'expires_at' => now()->addMonth(),
            'created_at' => now()->format('Y-m-d'),
        ]);
    }
    
}
