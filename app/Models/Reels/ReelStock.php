<?php

namespace App\Models\Reels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProductionRun;

class ReelStock extends Model
{
    protected $fillable = [
        'stock_code',
        'actual_code',
        'reel_id',
        'reel_provider_id',
        'reel_warehouse_id',
        'original_length',
        'balance_length',
        'cut_width',
        'purchase_price',
        'status',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'original_length' => 'decimal:3',
        'balance_length' => 'decimal:3',
        'cut_width' => 'decimal:3',
        'purchase_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });
        static::updating(fn($model) => $model->updated_by = auth()->id());
    }

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
    public function movements(): HasMany
    {
        return $this->hasMany(ReelStockMovement::class);
    }
    public function saleItems(): HasMany
    {
        return $this->hasMany(ReelSaleItem::class);
    }
    public function usages(): HasMany
    {
        return $this->hasMany(ReelStockUsage::class);
    }
    public function productionRuns(): HasMany { return $this->hasMany(ProductionRun::class); }
    public function activeProductionRun() { return $this->hasOne(ProductionRun::class)->where('status', 'in_progress'); }
}
