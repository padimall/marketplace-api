<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['user_id','product_id','quantity','status'];
}
