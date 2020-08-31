<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['username','password','email','address','phone'];
}
