<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoices_group_log extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['invoice_group_id','status'];
}
