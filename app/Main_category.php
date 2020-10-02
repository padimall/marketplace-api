<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Main_category extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['name','status','image'];
}
