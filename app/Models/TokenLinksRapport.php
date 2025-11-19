<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenLinksRapport extends Model
{
    use HasFactory;

    protected $fillable = ['token', 'documents', 'paths', 'expires_at', 'created_at'];
    protected $table = "token_links_rapport";
    public $timestamps = false;

    public static function generateTokenRapport( $token, $path ) {
        return self::create([
            'token' => $token,
            'paths' => $path,
            'expires_at' => now()->addMonth(),
            'created_at' => now()->format('Y-m-d'),
        ]);
    }
    
}
