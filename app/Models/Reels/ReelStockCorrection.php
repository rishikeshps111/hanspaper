<?php

namespace App\Models\Reels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReelStockCorrection extends Model
{
    protected $fillable = [
        'stock_batch_uuid', 'original_reel_id', 'original_reel_provider_id', 'original_reel_warehouse_id',
        'reel_id', 'reel_provider_id', 'reel_warehouse_id',
        'previous_quantity', 'corrected_quantity', 'quantity_change',
        'affected_stock_codes', 'reason', 'created_by',
    ];

    protected $casts = ['affected_stock_codes' => 'array'];

    public function reel(): BelongsTo
    {
        return $this->belongsTo(Reel::class);
    }

    public function originalReel(): BelongsTo
    {
        return $this->belongsTo(Reel::class, 'original_reel_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ReelProvider::class, 'reel_provider_id');
    }

    public function originalProvider(): BelongsTo
    {
        return $this->belongsTo(ReelProvider::class, 'original_reel_provider_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(ReelWarehouse::class, 'reel_warehouse_id');
    }

    public function originalWarehouse(): BelongsTo
    {
        return $this->belongsTo(ReelWarehouse::class, 'original_reel_warehouse_id');
    }
}
