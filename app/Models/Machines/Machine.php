<?php
namespace App\Models\Machines;
use App\Models\ProductionList;
use Illuminate\Database\Eloquent\Model;
use App\Models\Items\ProductionItemMaster;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_name',
        'status',
        'created_by',
        'updated_by',
    ];

    public function productionLists()
    {
        return $this->hasMany(ProductionList::class);
    }

    public function assignments()
    {
        return $this->hasMany(ProductionItemMaster::class, 'assigned_machine_id');
    }


}
