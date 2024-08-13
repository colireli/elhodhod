<?php

namespace Modules\Cargo\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiModel extends Model
{
    protected $table = 'api_models';
    use HasFactory;
    protected $fillable = ['name','code','img','api_token','user_guid','reference','client','phone','adresse','wilaya_id','commune','montant','remarque','produit','type_id','poids','stop_desk','stock','quantite','tracking','success','activity','is_archived'];

    /**
     * Get all of the companies for the Model
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'model_id', 'id');
    }
}
