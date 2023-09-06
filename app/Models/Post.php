<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable=[
        'negative','desc','image','user_id','reacts',
    ];


    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function reacts(){
        return $this->belongsToMany('App\Models\User');
    }
}
