<?php

namespace Modules\Cargo\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StopDesk extends Model
{
    use HasFactory;

    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'stopdesks';
    
    protected static function newFactory()
    {
        return \Modules\Cargo\Database\factories\StopDeskFactory::new();
    }
    public function state(){
        return $this->hasOne('Modules\Cargo\Entities\State', 'id' , 'state_id');
    }
    public function country(){
        return $this->hasOne('Modules\Cargo\Entities\Country', 'id' , 'country_id');
    }
}
