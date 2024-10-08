<?php

namespace Modules\Cargo\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Cargo\Entities\Branch;
use Modules\Cargo\Entities\Plan;
use Modules\Cargo\Entities\Staff;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Client extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'clients';

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('preview')->fit(Manipulations::FIT_CROP, 300, 300)->nonQueued();
    }

    protected static function newFactory()
    {
        return \Modules\Cargo\Database\factories\ClientFactory::new();
    }

    public function branch(){
        return $this->hasOne('Modules\Cargo\Entities\Branch', 'id', 'branch_id');
    }
    public function plan(){
        return $this->hasOne('Modules\Cargo\Entities\Plan', 'id', 'plan_id');
    }
    public function packages(){
        return $this->hasMany('Modules\Cargo\Entities\ClientPackage', 'client_id' , 'id');
    }

    public function getClients($query)
    {
        if(auth()->user()->role == 1){
            return $query->where('is_archived', 0);
        }elseif(auth()->user()->role == 3){
            $branch = Branch::where('user_id',auth()->user()->id)->pluck('id')->first();
        }elseif(auth()->user()->can('manage-customers') && auth()->user()->role == 0){
            $branch = Staff::where('user_id',auth()->user()->id)->pluck('branch_id')->first();
        }
        return $query->where('is_archived', 0)->where('branch_id', $branch);
    }
    public function addressess(){
        return $this->hasMany('Modules\Cargo\Entities\ClientAddress', 'client_id' , 'id');
    }

}
