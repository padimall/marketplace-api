<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['gate','method','method_code','status'];
}
