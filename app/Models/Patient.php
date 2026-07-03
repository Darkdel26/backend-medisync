<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Patient extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'num_matricule',
        'nom',
        'prenom',
        'sexe',
        'email',
        'mot_de_passe',
        'date_naissance',
        'telephone',
        'adresse',
        'statut',
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->mot_de_passe;
    }

    /**
     * Tout l'historique des consultations de ce patient.
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }
}
