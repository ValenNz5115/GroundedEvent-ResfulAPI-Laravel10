<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table="articles";
    protected $primaryKey="article_id";
    protected $fillable=['article_id','author','title','description','image'];
}
