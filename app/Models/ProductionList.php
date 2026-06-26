<?php

namespace App\Models;

use App\Models\Machines\Machine;
use App\Models\Employees\Employee;
use App\Models\Items\ProductionItemMaster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseOrders\PurchaseOrderMaster;

class ProductionList extends Model
{
    use SoftDeletes;

    protected $table = 'production_list';

    protected $fillable = [
        'production_item_master_id',
        'machine_id',
        'produced_by',
        'quantity',
        'real_id',
    ];

    // Relationships

    public function productionItemMaster()
    {
        return $this->belongsTo(ProductionItemMaster::class);
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
