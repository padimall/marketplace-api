<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoices_product extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['invoice_id','product_id','name','price','quantity'];
}
