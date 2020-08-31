<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['buyer_id','product_id','quantity','status'];
}
