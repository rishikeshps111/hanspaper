<?php

namespace App\Models;

use App\Models\Employees\Employee;
use App\Models\Items\ProductionItemMaster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingList extends Model
{
    use SoftDeletes;

    protected $table = 'packing_list';

    protected $fillable = [
        'production_item_master_id',
        'packed_by',
        'quantity',
    ];

    // Relationships

    public function purchaseOrderMaster()
    {
        return $this->belongsTo(ProductionItemMaster::class, 'production_item_master_id');
    }

    public function packedBy()
    {
        return $this->belongsTo(Employee::class, 'packed_by');
    }
}
