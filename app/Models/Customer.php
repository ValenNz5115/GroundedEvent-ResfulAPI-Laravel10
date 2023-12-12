<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table="customers";
    protected $primaryKey="customer_id";
    protected $fillable = [
        'username',
        'email',
        'password',
        'phone',
        'ttl',
        'city',
        'company',
        'gender',
        'image'
    ];
}
