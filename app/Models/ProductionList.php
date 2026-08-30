<?php

namespace App\Models;

use App\Models\Machines\Machine;
use App\Models\Employees\Employee;
use App\Models\Items\ProductionItemMaster;
use App\Models\Reels\ReelStockUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseOrders\PurchaseOrderMaster;

class ProductionList extends Model
{
    use SoftDeletes;

    protected $table = 'production_list';

    protected $fillable = [
        'production_item_master_id',
        'production_run_id',
        'machine_id',
        'produced_by',
        'quantity',
        'actual_quantity',
        'excess_stock_quantity',
        'real_id',
        'reel_stock_id',
        'core_id',
        'core_quantity',
    ];

    // Relationships

    public function productionItemMaster()
    {
        return $this->belongsTo(ProductionItemMaster::class);
    }

    public function productionRun() { return $this->belongsTo(ProductionRun::class); }

    public function reelStockUsage()
    {
        return $this->hasOne(ReelStockUsage::class, 'production_list_id');
    }

    public function core()
    {
        return $this->belongsTo(Core::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function producedBy()
    {
        return $this->belongsTo(Employee::class, 'produced_by');
    }

    public function real()
    {
        return $this->belongsTo(Real::class, 'real_id');
    }
}
