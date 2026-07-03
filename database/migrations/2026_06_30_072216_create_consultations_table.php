<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enregistrement de chaque consultation médicale : signes vitaux,
     * diagnostics, ordonnances et suivi.
     */
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            // Relations
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('medecin_id')->constrained('medecins')->restrictOnDelete();

            // Informations générales
            $table->dateTime('date_consultation');
            $table->string('type_consultation', 100);
            $table->text('motif_consultation')->nullable();
            $table->text('symptomes')->nullable();

            // Signes vitaux
            $table->decimal('temperature', 4, 1)->nullable();
            $table->string('tension_arterielle', 20)->nullable();
            $table->decimal('poids', 5, 2)->nullable();
            $table->integer('frequence_cardiaque')->nullable();
            $table->decimal('saruration_oxygene', 5, 2)->nullable();

            // Examens et diagnostic
            $table->text('examens_demandes')->nullable();
            $table->string('resultats_examens', 255)->nullable();
            $table->string('diagnostic_principal', 255)->nullable();
            $table->string('ordonnance', 255)->nullable();
            $table->text('observations_medicales')->nullable();

            // Suivi
            $table->enum('niveau_urgence', ['Faible', 'Moyen', 'Elevé'])->default('Moyen');
            $table->dateTime('prochain_rdv')->nullable();
            $table->string('localisation_dossier', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
