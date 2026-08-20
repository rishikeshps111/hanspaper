<?php

namespace App\Models\Reels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reel_brand_id',
        'reel_type_id',
        'reel_gsm_id',
        'width',
        'length',
        'unit_price',
        'selling_price',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'width' => 'decimal:2',
        'length' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ReelBrand::class, 'reel_brand_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ReelType::class, 'reel_type_id');
    }

    public function gsm(): BelongsTo
    {
        return $this->belongsTo(ReelGsm::class, 'reel_gsm_id');
    }

    public function stocks()
    {
        return $this->hasMany(ReelStock::class);
    }
}
