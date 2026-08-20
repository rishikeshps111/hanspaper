<?php

namespace App\Models\BarcodeWorkOrders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarcodeWorkOrderItem extends Model
{
    protected $fillable = ['number_of_rolls', 'stickers_per_roll', 'sticker_length', 'sticker_width', 'type', 'gap', 'gap_mm', 'is_printing', 'printing_colors', 'remarks'];

    protected $casts = ['is_printing' => 'boolean', 'sticker_length' => 'decimal:2', 'sticker_width' => 'decimal:2', 'gap_mm' => 'decimal:2'];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(BarcodeWorkOrder::class, 'barcode_work_order_id');
    }
}
