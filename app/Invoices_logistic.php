<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoices_logistic extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['invoice_id','logistic_id','resi','status'];
}
