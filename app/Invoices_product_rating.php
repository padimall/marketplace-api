<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoices_product_rating extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['invoice_product_id','star','description','show_name','censored_at','censored_reason','name'];
}
