<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoices_group extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['xendit_id','status','amount'];
}
