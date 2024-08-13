<?php

namespace Modules\Cargo\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyPlan extends Model
{
    use HasFactory;

    protected $fillable = ['title','branch_id'];

    /**
     * Get all of the fees for the Plan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fees(): HasMany
    {
        return $this->hasMany(CompanyPlanFee::class, 'plan_id', 'id');
    }
}
