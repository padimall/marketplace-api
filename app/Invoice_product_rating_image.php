<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice_product_rating_image extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['invoice_product_rating_id','image'];
}
