<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;
    protected $softDelete = true;
    protected $fillable = ['supplier_id','name','price','weight','description','category','stock','min_order','status','agent_id','admin_price'];
}
