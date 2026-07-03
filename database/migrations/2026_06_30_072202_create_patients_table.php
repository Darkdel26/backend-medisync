<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Comptes des patients suivis pour une ou plusieurs
     * maladies chroniques.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('num_matricule', 50)->unique();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->enum('sexe', ['M', 'F'])->default('M');
            $table->string('email', 175)->unique();
            $table->string('mot_de_passe');
            $table->date('date_naissance');
            $table->string('telephone', 20);
            $table->string('adresse', 175);
            $table->enum('statut', ['Actif', 'Inactif'])->default('Actif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
