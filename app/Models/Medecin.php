<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Medecin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'mot_de_passe',
        'specialite',
        'telephone',
        'num_ordre',
        'statut',
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    public function getAuthPassword(): string
    {
        return $this->mot_de_passe;
    }

    /**
     * Toutes les consultations effectuées par ce médecin.
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }
}
