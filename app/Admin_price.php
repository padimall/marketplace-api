<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Admin_price extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['up_to_price','addition_price'];
}
