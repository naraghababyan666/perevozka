<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
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

    public function manager(){
        return $this->belongsTo(Manager::class, 'id', 'company_id');
    }

    public function goods(){
        return $this->hasOne(GoodsOrders::class, 'id', 'company_id');
    }
    public function rides(){
        return $this->hasOne(RideOrders::class, 'id', 'company_id');
    }
    public function subscriptions(){
        return $this->hasOne(Subscriptions::class, 'id', 'company_id');
    }

    public function subs(){
        $data = Subscriptions::query()->where('company_id', Auth::id())->where('valid_until', '>', Carbon::now())->first();
        if($data){
             return $data['valid_until'];
        }else{
            return null;
        }
    }


}
