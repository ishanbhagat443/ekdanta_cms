<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
    ];

    /** Staff = anyone with a CMS role; customers have none. */
    public function isStaff(): bool
    {
        return $this->roles()->exists();
    }

    /**
     * AdminLTE: full name shown in the user menu.
     */
    public function adminlte_desc(): string
    {
        return $this->roles->pluck('name')->implode(', ');
    }

    /**
     * AdminLTE: profile link target for the navbar user menu.
     */
    public function adminlte_profile_url(): string
    {
        return 'cms/profile';
    }
}
