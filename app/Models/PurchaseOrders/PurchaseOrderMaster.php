<?php

namespace App\Models\PurchaseOrders;

use App\Models\User;
use App\Models\Currency;
use App\Models\Sale\Sale;
use App\Traits\FormatTime;
use App\Models\PackingList;
use App\Models\Party\Party;
use App\Models\StatusHistory;
use App\Models\ProductionList;
use App\Models\Items\ProductionItemMaster;
use App\Models\Dispatch\Dispatch;
use App\Traits\FormatsDateInputs;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Model;
use App\Models\Accounts\AccountTransaction;
use App\Models\PurchaseOrders\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\SalesRepresentatives\SalesRepresentative;
use Carbon\Carbon;

class PurchaseOrderMaster extends Model
{
    use HasFactory;

    use FormatsDateInputs;

    use FormatTime;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'customer_id',
        'representative_id',
        'po_date',
        'due_date',
        'purchase_order_remarks',
        'purchase_order_status',
        'mode_of_dispatch',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Insert & update User Id's
     * */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    /**
     * This method calling the Trait FormatsDateInputs
     * @return null or string
     * Use it as formatted_order_date
     * */
    public function getFormattedOrderDateAttribute()
    {
        return $this->toUserDateFormat($this->order_date); // Call the trait method
    }

    /**
     * This method calling the Trait FormatsDateInputs
     * @return null or string
     * Use it as formatted_due_date
     * */
    public function getFormattedDueDateAttribute()
    {
        return $this->toUserDateFormat($this->due_date); // Call the trait method
    }

    /**
     * This method calling the Trait FormatTime
     * @return null or string
     * Use it as format_created_time
     * */
    public function getFormatCreatedTimeAttribute()
    {
        return $this->toUserTimeFormat($this->created_at); // Call the trait method
    }

    /**
     * Define the relationship between Order and User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Define the relationship between Order and Party.
     *
     * @return BelongsTo
     */
    // public function party(): BelongsTo
    // {
    //     return $this->belongsTo(Party::class, 'party_id');
    // }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'customer_id');  // Updated from 'party_id' to 'customer_id'
    }



    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }
 public function productionmaster()
    {
        return $this->hasMany(ProductionItemMaster::class, 'purchase_order_id');
    }

 public function dispatchd()
    {
        return $this->hasMany(Dispatch::class, 'purchase_order_id');
    }



    /**
     * Define the relationship between Expense Payment Transaction & Expense table.
     *
     * @return MorphMany
     */
    public function paymentTransaction(): MorphMany
    {
        return $this->morphMany(PaymentTransaction::class, 'transaction');
    }

    public function sale(): HasOne
    {
        return $this->hasOne(Sale::class);
    }

    /**
     * Define the relationship between Item Transaction & Items table.
     *
     * @return MorphMany
     */
    public function accountTransaction(): MorphMany
    {
        return $this->morphMany(AccountTransaction::class, 'transaction');
    }

    public function getTableCode()
    {
        return $this->order_code;
    }


    /**
     * Define the relationship between Status History & Sale Order table.
     *
     * @return MorphMany
     *
     * where 'statusable' is the method, which this will call
     */
    public function statusHistory(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'statusable');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
    
    public function representative(): BelongsTo
    {
        return $this->belongsTo(SalesRepresentative::class, 'representative_id');
    }

    public function getGapInDaysBadgeAttribute()
    {
        if (!$this->po_date) {
            return '<span class="badge bg-secondary">N/A</span>';
        }

        $poDate = Carbon::parse($this->po_date);
        $gap = today()->diffInDays($poDate) . ' days';
        $isCompleted = in_array($this->purchase_order_status, ['Completed', 'Dispatched']);
        $badgeClass = $isCompleted ? 'bg-success' : 'bg-danger';

        return '<span class="badge ' . $badgeClass . '" style="font-size: 0.9rem; padding: 8px 12px; border-radius: 8px;">'
            . e($gap) .
            '</span>';
    }

}
