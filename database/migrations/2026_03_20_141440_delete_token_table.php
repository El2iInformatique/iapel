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
        // Supprime la table uniquement si elle existe dans la base de données
        Schema::dropIfExists('tokens');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On recrée la structure de base si on annule la migration
        // (Attention : la table sera vide, les anciennes données sont perdues)
         Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 40)->unique();
            $table->unsignedBigInteger('organisation_id')->nullable();
            $table->string('client_email');
            $table->unsignedBigInteger('devis_id')->nullable();
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->string('titre');
            $table->decimal('montant_HT', 10, 2);
            $table->decimal('montant_TVA', 10, 2);
            $table->decimal('montant_TTC', 10, 2);
            $table->timestamps();
        });
    }
};
