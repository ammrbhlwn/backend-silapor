<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'role',
    ];

    protected $casts = [
        'role' => UserRole::class,
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function lapangans()
    {
        return $this->hasMany(Lapangan::class, 'user_id');
    }

    public function favorites()
    {
        return $this->belongsToMany(Lapangan::class, 'favorite', 'user_id', 'lapangan_id');
    }

    public function transaksiBookings()
    {
        return $this->hasMany(TransaksiBooking::class, 'user_id');
    }
}
