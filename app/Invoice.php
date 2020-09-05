<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['buyer_id','supplier_id','amount','status'];
}
