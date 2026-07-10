<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    #[Fillable([
        'Martin',
        'email',
        '1111',
        'tenant_id',
        '0985243599',
        'google_id',
        'oauth_provider',
        'oauth_token',
        'avatar_url',
        'role',
        'is_active',
        'last_login_at',
    ])]
    #[Hidden([
        'password',
        'remember_token',
    ])]

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    // You can add mutators/accessors if needed
    // For example, to automatically hash password if set
    // protected function password(): Attribute
    // {
    //     return Attribute::make(
    //         set: fn (string $value) => bcrypt($value),
    //     );
    // }
}