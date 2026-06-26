<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RealStock extends Model
{
    use SoftDeletes;

    protected $table = 'real_stocks';

    protected $fillable = [
        'real_id',
        'type',
        'quantity',
        'status',
        'total_length',
        'bal_length',
        'remarks',
        'stock_status'
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Each stock entry belongs to a real
     */
    public function real()
    {
        return $this->belongsTo(Real::class, 'real_id');
    }
}
