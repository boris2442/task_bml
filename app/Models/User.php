<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'matricule'  // On ajoute matricule dans fillable
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        // Générer le matricule automatiquement à la création
        static::creating(function ($user) {
            $user->matricule = self::generateMatricule();
        });
    }

    /**
     * Génère un matricule unique de 10 caractères (lettres et chiffres)
     * Format: EMP suivi de 7 caractères aléatoires (ex: EMP-A7F92B1)
     */
    public static function generateMatricule(): string
    {
        do {
            // EMP + 7 caractères aléatoires (lettres majuscules + chiffres)
            $matricule = 'BML-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 7));
        } while (self::where('matricule', $matricule)->exists()); // Vérifier l'unicité

        return $matricule;
    }

    /**
     * Calculer le nombre total d'heures travaillées (validées et approuvées)
     * Uniquement les présences avec statut 'validee' sont comptabilisées
     */
    public function getTotalHeuresAttribute(): float
    {
        $presences = $this->presences()
            ->where('statut', 'validee')
            ->get();

        $totalHeures = 0;
        foreach ($presences as $presence) {
            if ($presence->heure_depart) {
                $totalHeures += $presence->heure_depart->diffInHours($presence->heure_arrivee);
            }
        }

        return $totalHeures;
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function approbations()
    {
        return $this->hasMany(Approbation::class, 'admin_id');
    }

    public function workSchedule()
    {
        return $this->hasOne(WorkSchedule::class);
    }
}
