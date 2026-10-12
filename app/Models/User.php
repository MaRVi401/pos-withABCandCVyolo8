<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $user_id
 * @property int $id
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password_hash',
        'phone',
        'address',
        'is_active',
        'created_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Memberitahu Laravel bahwa kolom password menggunakan 'password_hash'
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * DEFINISI RELASI KE MODEL ROLE (Ini yang menyebabkan error)
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}