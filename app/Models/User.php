<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLE_NURSE = 'perawat';
    public const ROLE_DOCTOR = 'dokter';

    protected $fillable = ['name', 'nip', 'email', 'phone', 'role', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function isDoctor(): bool
    {
        return $this->role === self::ROLE_DOCTOR;
    }

    public function homeUrl(): string
    {
        return route($this->isDoctor() ? 'dokter.dashboard' : 'perawat.dashboard');
    }

    public function roleLabel(): string
    {
        return $this->isDoctor() ? 'Dokter' : 'Perawat';
    }
}
