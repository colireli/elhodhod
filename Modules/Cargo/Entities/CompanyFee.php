<?php

namespace Modules\Cargo\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyFee extends Model
{

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
            'delivery_fee' => $company->default_fee,
            'return_fee' => $company->default_return,
        ]);
        return $fee;
    }
}
