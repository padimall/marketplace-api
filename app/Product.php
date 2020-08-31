<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['supplier_id','name','price','weight','description','category','stock','status'];
}
