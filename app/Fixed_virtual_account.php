<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fixed_virtual_account extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $fillable = ['user_id','fva_id','name','bank_code','account_number','expiration_date'];

}
