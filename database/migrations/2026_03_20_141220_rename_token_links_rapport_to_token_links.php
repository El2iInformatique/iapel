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
        if (Schema::hasTable("token_links_rapport")) {
            // On renomme d'abord la table
            Schema::rename('token_links_rapport', 'token_links');
        }
    

        // On ajoute le nouveau champ 'document' à la table renommée
        Schema::table('token_links', function (Blueprint $table) {
            // On le rend "nullable" pour ne pas bloquer sur les anciennes données
            $table->string('documents')->nullable(); 
            // Note: Vous pouvez changer le type (text, json, etc.) 
            // et utiliser ->after('nom_colonne') pour choisir son emplacement
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        if (Schema::hasTable("token_links")) {
            //  On supprime d'abord la colonne de la nouvelle table
            Schema::table('token_links', function (Blueprint $table) {
                $table->dropColumn('documents');
            });
            
            // On redonne à la table son nom d'origine
            Schema::rename('token_links', 'token_links_rapport');
        }

    }
};
