<?php

namespace Modules\Cargo\Entities;

use Modules\Cargo\Entities\State;
use Modules\Cargo\Entities\Plan;
// add_update_v1
//  START_CODE
use Modules\Cargo\Entities\Company;
use Modules\Cargo\Entities\PlanAreaFee;
use Illuminate\Database\Eloquent\Relations\HasMany;
// END_CODE
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Illuminate\Database\Eloquent\Relations\Pivot;

class PlanFee extends Model
{
    use HasFactory;
    protected $table = 'plan_fee';
    // edit_update_v1
    //  START_CODE
    protected $with = ['plan:id,title','state:id,name'];
    // END_CODE
    protected $guarded = [];
    // add_update_v1
    //  START_CODE
    protected $fillable = ['company','state_id','plan_id','home_fee','desk_fee','return_fee','recovery_rate','company','created_at','updated_at'];
    // END_CODE

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
        return $this->belongsTo(Plan::class,'plan_id');
    }
    // add_update_v1
    //  START_CODE
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company');
    }

    public function areas(): HasMany
    {
        return $this->hasMany(PlanAreaFee::class);
    }
    // END_CODE
}
