<?php

namespace App\Models\Reels;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ReelWarehouse extends ReelSetting
{
    protected $casts = [
        'is_active' => 'boolean',
        'warehouse_type' => 'string',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(ReelStock::class);
    }
}
