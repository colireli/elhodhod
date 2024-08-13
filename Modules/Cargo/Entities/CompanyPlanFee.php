<?php

namespace Modules\Cargo\Entities;

use Modules\Cargo\Entities\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPlanFee extends Model
{
    use HasFactory;

    protected $with = 'state:id,name';

    /**
     * Get the state that owns the PlanFee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the plan that owns the PlanFee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(CompanyPlan::class, 'plan_id');
    }
}
