<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Company extends Authenticatable
{
    use HasFactory, HasApiTokens, HasFactory, Notifiable;

    protected $table = 'companies';

    protected $fillable = [
        'email', 'password', 'phone_number', 'role_id', 'is_admin', 'company_name', 'inn', 'ogrn',
        'legal_address', 'postal_address', 'logo_url', 'favorites'
    ];

    public const IS_OWNER = 1;
    public const IS_DRIVER = 2;
    public const IS_OWNER_AND_DRIVER = 3;
    public const IS_ADMIN = 4;

    protected $hidden = [
        'password'
    ];
}
