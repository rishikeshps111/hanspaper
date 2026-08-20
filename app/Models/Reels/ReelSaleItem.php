<?php

namespace App\Models\Reels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReelSaleItem extends Model
{
    protected $fillable = ['reel_sale_id', 'reel_stock_id', 'length', 'unit_price', 'discount', 'total', 'balance_before', 'balance_after'];
    public function sale(): BelongsTo { return $this->belongsTo(ReelSale::class, 'reel_sale_id'); }
    public function stock(): BelongsTo { return $this->belongsTo(ReelStock::class, 'reel_stock_id'); }
}
