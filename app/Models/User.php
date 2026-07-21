<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $appends = ['avatar_full_path'];

    protected $fillable = [
        'first_name', 'last_name', 'full_name', 'slug', 'email', 'phone',
        'password', 'role', 'role_id', 'address', 'area', 'city', 'state',
        'country', 'country_code', 'zipcode', 'latitude', 'longitude',
        'timezone', 'avatar', 'bio', 'device_token', 'device_type', 'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function getAvatarFullPathAttribute()
    {
        if ($this->avatar) {
            return asset($this->avatar);
        }
        return '';
    }
}
