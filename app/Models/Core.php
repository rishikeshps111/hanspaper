<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Core extends Model
{
    protected $fillable = ['code', 'size_mm', 'quantity', 'is_active', 'created_by', 'updated_by'];
    protected $casts = ['size_mm' => 'decimal:2', 'quantity' => 'integer', 'is_active' => 'boolean'];

    public function movements(): HasMany { return $this->hasMany(CoreStockMovement::class); }
    public function productionRuns(): HasMany { return $this->hasMany(ProductionRun::class); }
}
