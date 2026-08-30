<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PackingMaterialStockMovement extends Model
{
    protected $fillable=['packing_material_id','transaction_type','quantity_change','quantity_before','quantity_after','reference_type','reference_id','remarks','created_by'];
}
