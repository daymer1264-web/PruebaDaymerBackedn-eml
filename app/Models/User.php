<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Remueve: use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Remueve: , SoftDeletes

    protected $fillable = [
        'nombres',
        'apellidos',
        'email',
        'telefono',
        'password',
        'estado',
        'fecha_registro',
        'fecha_ultima_modificacion',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_registro' => 'datetime',
            'fecha_ultima_modificacion' => 'datetime',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeOrdenAlfabetico($query)
    {
        return $query->orderBy('apellidos', 'asc')
                     ->orderBy('nombres', 'asc');
    }

    protected static function boot()
    {
        parent::boot();

        // Al crear un usuario
        static::creating(function ($user) {
            if (empty($user->fecha_registro)) {
                $user->fecha_registro = now();
            }
            if (empty($user->fecha_ultima_modificacion)) {
                $user->fecha_ultima_modificacion = now();
            }
        });

        // Al actualizar un usuario
        static::updating(function ($user) {
            $user->fecha_ultima_modificacion = now();
        });
    }
}