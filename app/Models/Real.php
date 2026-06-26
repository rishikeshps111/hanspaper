<?php

namespace App\Models;

use App\Models\Items\Brand;
use App\Models\Items\ItemCategory;
use App\Models\RealStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Real extends Model
{
    use SoftDeletes;

    protected $table = 'reals';

    protected $primaryKey = 'id';

    protected $fillable = [
        'brand',
        'category',
        'real_no',
        'gsm',
        'subcode',
        'width',
        'length',
        'weight',
        'is_active',
        'current_status'
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'width'       => 'decimal:2',
        'length'      => 'decimal:2',
        'weight'      => 'decimal:2',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    protected $attributes = [
        'is_active' => 1,
    ];

    public function brandRelation()
    {
        return $this->belongsTo(Brand::class, 'brand', 'id');
    }

    public function categoryRelation()
    {
        return $this->belongsTo(ItemCategory::class, 'category', 'id');
    }

    public function productionLists()
    {
        return $this->hasMany(ProductionList::class, 'real_id', 'id');
    }

    public function getFormattedIdAttribute()
    {
        return 'REAL' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }

    public function stocks()
    {
        return $this->hasMany(RealStock::class, 'real_id');
    }
     public function stocksRelation()
    {
        return $this->belongsTo(RealStock::class, 'id', 'real_id');
    }


    public function getTotalStockAttribute()
    {
        return $this->stocks()
           // ->where('type', 'in')
            ->sum('quantity');
    }

    // Total OUT stock
    public function getUsedStockAttribute()
    {
        return $this->stocks()
           // ->where('type', 'out')
            ->sum('quantity');
    }

    // Available stock (IN - OUT)
    public function getAvailableStockAttribute()
    {
        return $this->total_stock - $this->used_stock;
    }

    // Fully used stock
    public function getFullUsedStockAttribute()
    {
        return $this->stocks()
            ->where('type', 'out')
            ->where('status', 'full')
            ->sum('quantity');
    }

    // Bit stock
    public function getBitStockAttribute()
    {
        return $this->stocks()
            ->where('type', 'out')
            ->where('status', 'bit')
            ->sum('quantity');
    }
}
