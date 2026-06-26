<?php

namespace App\Models\Employees;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use App\Models\Items\ProductionItemMaster;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\PackingList;
use App\Models\ProductionList;

class Employee extends Authenticatable
{
  protected $fillable = [
    'full_name',
    'mobile',
    'email',
    'status',
    'created_by'
  ];

  public function productionAssignments()
  {
    return $this->hasMany(ProductionItemMaster::class, 'assigned_production_user_id');
  }

  public function packingAssignments()
  {
    return $this->hasMany(ProductionItemMaster::class, 'assigned_packing_user_id');
  }
  
  public function productions()
  {
    return $this->hasMany(ProductionList::class, 'produced_by');
  }

  public function packings()
  {
    return $this->hasMany(PackingList::class, 'packed_by');
  }
}