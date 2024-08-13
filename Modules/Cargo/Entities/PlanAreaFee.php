<?php

namespace Modules\Cargo\Entities;

use Modules\Cargo\Entities\Area;
use Modules\Cargo\Entities\PlanFee;
use Modules\Cargo\Entities\Plan;

use Modules\Cargo\Entities\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Database\Eloquent\Relations\Pivot;

class PlanAreaFee extends Model
{
    use HasFactory;
    protected $table = 'plan_area_fee';

    protected $with = ['area:id,name'];

    protected $guarded = [];

    protected $fillable = ['company','plan_fee_id'];

    /**
     * Get the area that owns the PlanFee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class,'area_id');
    }

    /**
     * Get the plan that owns the PlanAreaFee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class,'plan_id');
    }

    /**
     * Get the plan that owns the PlanFee
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function planfee(): BelongsTo
    {
        return $this->belongsTo(PlanFee::class,'plan_fee_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company');
    }

}
