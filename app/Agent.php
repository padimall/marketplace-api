<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['user_id','name','phone','agent_code','image'];
}
