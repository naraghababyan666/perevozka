<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Favorites extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'order_id', 'order_type'];

    public function relation(){
        if(Auth::user()['role_id'] == Company::IS_OWNER){
            return $this->belongsTo(RideOrders::class, 'order_id', 'id');
        }else if (Auth::user()['role_id'] == Company::IS_DRIVER){
            return $this->belongsTo(GoodsOrders::class, 'order_id', 'id');
        }else{
            $orders = $this->belongsTo(GoodsOrders::class, 'order_id', 'id');
            $rides = $this->belongsTo(RideOrders::class, 'order_id', 'id');
            return ['orders' => $orders, 'rides' => $rides] ;
        }
    }

    public function goods(){
        return $this->belongsTo(GoodsOrders::class, 'order_id', 'id');
    }
    public function rides(){
        return $this->belongsTo(RideOrders::class, 'order_id', 'id');
    }
}
