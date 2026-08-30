<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoreStockMovement extends Model
{
    protected $fillable = ['core_id', 'transaction_type', 'quantity_change', 'quantity_before', 'quantity_after', 'reference_type', 'reference_id', 'remarks', 'created_by'];
    public function core(): BelongsTo { return $this->belongsTo(Core::class); }
}
