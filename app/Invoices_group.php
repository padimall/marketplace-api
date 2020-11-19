<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoices_group extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['external_payment_id','payment_id','status','amount'];
}
