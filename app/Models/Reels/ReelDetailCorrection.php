<?php

namespace App\Models\Reels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReelDetailCorrection extends Model
{
    protected $fillable = ['reel_id', 'before_values', 'after_values', 'reason', 'created_by'];

    protected $casts = ['before_values' => 'array', 'after_values' => 'array'];

    public function reel(): BelongsTo
    {
        return $this->belongsTo(Reel::class);
    }
}
