<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['image','type'];
}
