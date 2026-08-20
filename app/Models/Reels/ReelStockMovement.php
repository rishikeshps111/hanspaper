<?php

namespace App\Models\Reels;

use App\Models\Party\Party;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReelStockMovement extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'batch_uuid', 'reel_stock_id', 'reel_provider_id', 'transaction_type', 'stock_status', 'length', 'balance_before', 'balance_after',
        'reference_type', 'reference_id', 'customer_id', 'reel_warehouse_id', 'remarks', 'created_by', 'created_at',
    ];
    protected $casts = ['created_at' => 'datetime'];
    protected static function booted(): void
    {
        static::creating(function (self $movement) {
            if (!$movement->reel_provider_id && $movement->reel_stock_id) {
                $movement->reel_provider_id = ReelStock::whereKey($movement->reel_stock_id)->value('reel_provider_id');
            }
        });
    }
    public function stock(): BelongsTo { return $this->belongsTo(ReelStock::class, 'reel_stock_id'); }
    public function provider(): BelongsTo { return $this->belongsTo(ReelProvider::class, 'reel_provider_id'); }
    public function warehouse(): BelongsTo { return $this->belongsTo(ReelWarehouse::class, 'reel_warehouse_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Party::class, 'customer_id'); }
    public function reference(): MorphTo { return $this->morphTo(); }
}
