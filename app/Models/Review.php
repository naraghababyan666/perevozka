<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        "writer_id",
        "company_id",
        "review_text",
        'is_published'
    ];

    public const INACTIVE = 0;
    public const DECLINED = 1;
    public const CONFIRMED = 2;

    public function writer(){
        return $this->hasMany(Company::class, 'id', 'writer_id');
    }
    public function company(){
        return $this->hasMany(Company::class, 'id', 'company_id');
    }
}
