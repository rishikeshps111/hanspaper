<?php

namespace App\Models\Dispatch;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Party\Party;
use App\Traits\FormatsDateInputs;
use App\Traits\FormatTime;
use App\Models\PaymentTransaction;
use App\Models\Sale\Sale;
use App\Models\Accounts\AccountTransaction;
use App\Models\Currency;
use App\Models\StatusHistory;
use App\Models\PurchaseOrders\PurchaseOrderItem;
use App\Models\PurchaseOrders\PurchaseOrder;
use App\Models\PurchaseOrders\PurchaseOrderMaster;
use App\Models\Items\ProductionItemMaster;
class Dispatch extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'customer_id',
        'remarks',
        'mode_of_delivery',
        'status',
        'purchase_order_identifier',
        'dispatch_order',
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrderMaster::class);
    }

    public function customer()
    {
        return $this->belongsTo(Party::class);
    }
      public function ProductionItemMaster()
    {
        return $this->hasMany(ProductionItemMaster::class, 'dispatches_id');
    }
    
      public function purchaseOrderItem()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'dispatches_id');
    }
}
