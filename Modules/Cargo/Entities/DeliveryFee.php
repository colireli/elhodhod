<?php

namespace Modules\Cargo\Entities;

use Illuminate\Database\Eloquent\Model;

class DeliveryFee extends Model
{

    protected $hidden = [
            'company_id',
            'active',
            'area_id',
        ];

    protected $guarded=[];
    public function company(){
        return $this->belongsTo(Company::class);

    }


    public function area(){
        return $this->belongsTo(Area::class);
    }

    static public function defaultFee($company_id)
    {
        $company = Company::findOrFail($company_id);
        $fee = new Self();
        $fee->fill([
            'delivery_fee' => $company->default_driver_fee,
            'return_fee' => $company->default_driver_return,
        ]);
        return $fee;
    }

}
