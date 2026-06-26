<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseOrders\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_item_id',
        'is_damaged',
        'reason'
    ];

    public function item()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }
}
