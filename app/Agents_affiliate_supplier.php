<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agents_affiliate_supplier extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['supplier_id','agent_id'];
}
