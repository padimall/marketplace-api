<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoices_payment extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['invoice_id','payment_id','pay_at','status'];
}
