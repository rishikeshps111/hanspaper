<?php

namespace App\Models\Reels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReelStockCorrection extends Model
{
    protected $fillable = [
        'stock_batch_uuid', 'reel_id', 'reel_provider_id', 'reel_warehouse_id',
        'previous_quantity', 'corrected_quantity', 'quantity_change',
        'affected_stock_codes', 'reason', 'created_by',
    ];

    protected $casts = ['affected_stock_codes' => 'array'];

    public function reel(): BelongsTo
    {
        return $this->belongsTo(Reel::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ReelProvider::class, 'reel_provider_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(ReelWarehouse::class, 'reel_warehouse_id');
    }
}
