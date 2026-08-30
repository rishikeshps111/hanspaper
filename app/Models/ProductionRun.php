<?php

namespace App\Models;

use App\Models\Employees\Employee;
use App\Models\Items\ProductionItemMaster;
use App\Models\Machines\Machine;
use App\Models\Reels\ReelStock;
use App\Models\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRun extends Model
{
    protected $fillable = [
        'production_id', 'reel_stock_id', 'machine_id', 'production_user_id', 'core_id', 'core_quantity', 'source_reel_status',
        'output_roll_width', 'roll_length', 'production_quantity', 'status', 'active_key', 'started_at', 'finished_at',
        'started_by', 'finished_by',
    ];

    protected $casts = [
        'output_roll_width' => 'decimal:3', 'roll_length' => 'decimal:3', 'production_quantity' => 'decimal:3',
        'production_id' => 'integer', 'reel_stock_id' => 'integer', 'machine_id' => 'integer',
        'production_user_id' => 'integer', 'core_id' => 'integer', 'core_quantity' => 'integer', 'active_key' => 'integer',
        'started_at' => 'datetime', 'finished_at' => 'datetime',
    ];

    public function production(): BelongsTo { return $this->belongsTo(ProductionItemMaster::class, 'production_id'); }
    public function reelStock(): BelongsTo { return $this->belongsTo(ReelStock::class); }
    public function machine(): BelongsTo { return $this->belongsTo(Machine::class); }
    public function productionUser(): BelongsTo { return $this->belongsTo(Employee::class, 'production_user_id'); }
    public function core(): BelongsTo { return $this->belongsTo(Core::class); }
}
