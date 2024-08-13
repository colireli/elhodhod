<?php

namespace Modules\Cargo\Entities;
use Modules\Cargo\Entities\ApiModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    protected $table = 'companies';
    protected $with = ['model:id,name'];
    use HasFactory;

    protected $fillable = ['name','code','img','model_id','plan_id','company_fee','branch_id','api_token','user_guid','create_order','valid_order','delete_order','tracking_order','is_archived'];

    /**
     * Get all of the fees for the Plan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fees(): HasMany
    {
        return $this->hasMany(Modules\Cargo\Entities\PlanFee::class, 'company', 'id');
    }

     /**
     * Get the model that owns the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(apiModel::class, 'model_id');
    }

    public function company_fees(){
		return $this->hasMany('Modules\Cargo\Entities\CompanyFee')->where('is_archived',0);
	}

    public function delivery_fees(){
		return $this->hasMany('Modules\Cargo\Entities\DeliveryFee')->where('is_archived',0);
	}
    public function state(){
		return $this->hasOne('Modules\Cargo\Entities\State')->where('is_archived',0);
	}
    public function area(){
		return $this->hasOne('Modules\Cargo\Entities\Area')->where('is_archived',0);
	}
}
