<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_client', function (Blueprint $table) {
            $table->id();
            /*
             * Stock le token hasher que l'on va comparer avec le token hasher envoyer par l'entrepise
             * Le token non hasher seras envoyer une seul fois a l'entreprise et il devrons nous l'envoyer
             * La comparaison se feras avec sha256
            */
            $table->string('token_hash')->unique();
            $table->string('entreprise', 40);
            $table->boolean("is_active")->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('created_at');
        });
    }
    

    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
