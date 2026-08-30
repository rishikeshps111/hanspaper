<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PackingMaterial extends Model
{
    protected $fillable=['type','code','name','capacity','quantity','is_active','created_by','updated_by'];
    protected $casts=['capacity'=>'integer','quantity'=>'integer','is_active'=>'boolean'];
    public function movements(): HasMany { return $this->hasMany(PackingMaterialStockMovement::class); }
}
