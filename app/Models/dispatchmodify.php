<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Dispatch\Dispatch;

class dispatchmodify extends Model
{
    use HasFactory;

      protected $table = 'cf'; // If different from plural model name
       protected $fillable = [
        'purchase_order_id',
        'purchase_order_items_id',
        'dispatches_id',
        'total_qty',
        'required_qty',
        'status',
        'created_at',
        'updated_at'
    ];
    

}
