<?php

namespace App\Models\BarcodeWorkOrders;

use App\Models\Party\Party;
use App\Models\SalesRepresentatives\SalesRepresentative;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarcodeWorkOrder extends Model
{
    protected $fillable = ['code', 'customer_id', 'representative_id', 'work_order_date', 'due_date', 'status', 'completed_date', 'dispatched_date', 'delivered_date', 'created_by', 'updated_by'];

    protected $casts = ['work_order_date' => 'date', 'due_date' => 'date', 'completed_date' => 'date', 'dispatched_date' => 'date', 'delivered_date' => 'date'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'customer_id');
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(SalesRepresentative::class, 'representative_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BarcodeWorkOrderItem::class);
    }
}
