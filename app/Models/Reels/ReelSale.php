<?php

namespace App\Models\Reels;

use App\Models\Party\Party;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReelSale extends Model
{
    protected $fillable = [
        'sale_code', 'invoice_number', 'customer_id', 'sale_date', 'subtotal', 'discount',
        'is_gst_applicable', 'sgst_percentage', 'sgst_amount',
        'cgst_percentage', 'cgst_amount', 'total', 'remarks',
    ];
    protected $casts = [
        'sale_date' => 'date', 'subtotal' => 'decimal:2', 'discount' => 'decimal:2',
        'is_gst_applicable' => 'boolean', 'sgst_percentage' => 'decimal:2',
        'sgst_amount' => 'decimal:2', 'cgst_percentage' => 'decimal:2',
        'cgst_amount' => 'decimal:2', 'total' => 'decimal:2',
    ];
    protected static function booted(): void
    {
        static::creating(function ($model) { $model->created_by = auth()->id(); $model->updated_by = auth()->id(); });
        static::updating(fn ($model) => $model->updated_by = auth()->id());
    }
    public function customer(): BelongsTo { return $this->belongsTo(Party::class, 'customer_id'); }
    public function items(): HasMany { return $this->hasMany(ReelSaleItem::class); }
}
