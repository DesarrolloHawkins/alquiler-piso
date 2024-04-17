<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    // /**
    //  * The roles that belong to the user.
    //  */
    // public function roles()
    // {
    //     // Asumiendo que existe una tabla 'roles' y la relación es muchos-a-muchos
    //     return $this->belongsToMany(Role::class);
    // }

    public function redirectToDashboard( $reservasPendientes )
    {
        switch ($this->role) {
            case 'ADMIN':
                return view('admin.dashboard', compact('reservasPendientes'));
            case 'USER':
                return view('user.dashboard', compact('reservasPendientes'));
            default:
                abort(403, 'No tienes permiso para acceder a esta página.');
        }
    }

}
