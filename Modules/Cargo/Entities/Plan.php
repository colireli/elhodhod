<?php

namespace Modules\Cargo\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'branch_id'];

    /**
     * Get all of the fees for the Plan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fees(): HasMany
    {
        return $this->hasMany(PlanFee::class);
    }
    // add_update_v1
    //  START_CODE

    /**
     * Get all of the areas for the Plan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function areas(): HasMany
    {
        return $this->hasMany(PlanAreaFee::class);
    }

    public function stopdesks(): HasMany
    {
        return $this->hasMany(PlanStopDeskFee::class);
    }

    // END_CODE

}
