<?php
namespace App\Models\Items;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use App\Models\Tax;
use App\Models\User;
use App\Models\Unit;
use App\Models\Items\Item;
use App\Models\Items\ItemCategory;
use App\Models\Items\ItemGeneralQuantity;
use App\Models\Items\ProductionItemMaster;
class ProductionItem extends Model
{
    use HasFactory;

     protected $fillable = [
        'production_id', 'item_id', 'requested_qty', 'entered_qty', 'remaining_qty', 'status'
    ];

    /**
     * Get the production master that owns the production item.
     */
    public function productionMaster(): BelongsTo
    {
        return $this->belongsTo(ProductionItemMaster::class, 'production_id', 'id');
    }

    /**
     * Get the item associated with this production item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

}
