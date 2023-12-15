<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory;

    public $timestamps = true;
    protected $table = "customers";
    protected $primaryKey = "customer_id";

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'phone',
        'ttl',
        'city',
        'company',
        'gender',
        'image'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guarded = [
        'customer_id',
    ];
}
