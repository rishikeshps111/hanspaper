<?php

namespace App\Models\Reels;

use App\Models\Items\ProductionItemMaster;
use App\Models\Machines\Machine;
use App\Models\ProductionList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReelStockUsage extends Model
{
    protected $fillable = [
        'production_id',
        'production_run_id',
        'production_list_id',
        'reel_stock_id',
        'source_status',
        'calculated_status',
        'resulting_status',
        'status_selection_type',
        'source_width',
        'output_roll_width',
        'roll_length',
        'production_quantity',
        'consumed_length',
        'output_roll_count',
        'total_output_length',
        'width_waste',
        'balance_before',
        'balance_after',
        'remaining_output_length',
        'physical_remaining_length',
        'wastage_output_length',
        'physical_wastage_length',
        'machine_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'source_width' => 'decimal:3',
        'output_roll_width' => 'decimal:3',
        'roll_length' => 'decimal:3',
        'production_quantity' => 'decimal:3',
        'consumed_length' => 'decimal:3',
        'total_output_length' => 'decimal:3',
        'width_waste' => 'decimal:3',
        'balance_before' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'remaining_output_length' => 'decimal:3',
        'physical_remaining_length' => 'decimal:3',
        'wastage_output_length' => 'decimal:3',
        'physical_wastage_length' => 'decimal:3',
    ];

    public function production(): BelongsTo
    {
        return $this->belongsTo(ProductionItemMaster::class, 'production_id');
    }
    public function productionRun(): BelongsTo { return $this->belongsTo(\App\Models\ProductionRun::class); }
    public function productionList(): BelongsTo
    {
        return $this->belongsTo(ProductionList::class);
    }
    public function stock(): BelongsTo
    {
        return $this->belongsTo(ReelStock::class, 'reel_stock_id');
    }
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
