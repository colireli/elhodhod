<?php

namespace Modules\Cargo\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;



class Receiver extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'receivers';


    protected static function newFactory()
    {
        return \Modules\Cargo\Database\factories\ReceiverFactory::new();
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

}
