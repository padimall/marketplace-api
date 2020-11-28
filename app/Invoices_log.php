<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoices_log extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['invoice_id','status'];
}
