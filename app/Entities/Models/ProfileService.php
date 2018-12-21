<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileService extends Model
{
    protected  $table ='profiles_service';

    protected $fillable = [
        'service_id',
        'profile_id',
    ];
    public $timestamps = false;
}
