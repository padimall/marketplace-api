<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['buyer_id','name','phone'];
}
