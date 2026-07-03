<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'medecin_id',
        'date_consultation',
        'type_consultation',
        'motif_consultation',
        'symptomes',
        'temperature',
        'tension_arterielle',
        'poids',
        'frequence_cardiaque',
        'saruration_oxygene',
        'examens_demandes',
        'resultats_examens',
        'diagnostic_principal',
        'ordonnance',
        'observations_medicales',
        'niveau_urgence',
        'prochain_rdv',
        'localisation_dossier',
    ];

    protected function casts(): array
    {
        return [
            'date_consultation' => 'datetime',
            'prochain_rdv' => 'datetime',
            'temperature' => 'decimal:1',
            'poids' => 'decimal:2',
            'saruration_oxygene' => 'decimal:2',
        ];
    }

    protected $appends = [
        'ordonnance_url',
        'resultats_examens_url'
    ];

    public function getOrdonnanceUrlAttribute()
    {
        return $this->ordonnance
            ? asset('storage/' . $this->ordonnance)
            : null;
    }

    public function getResultatsExamensUrlAttribute()
    {
        return $this->resultats_examens
            ? asset('storage/' . $this->resultats_examens)
            : null;
    }

    /**
     * Le patient concerné par cette consultation.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Le médecin ayant effectué cette consultation.
     */
    public function medecin(): BelongsTo
    {
        return $this->belongsTo(Medecin::class);
    }
}
