<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['user_id','supplier_id','amount','status','agent_id'];
}
