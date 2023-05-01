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
        "review_text"
    ];
    public function writer(){
        return $this->hasMany(Company::class, 'id', 'writer_id');
    }
}
