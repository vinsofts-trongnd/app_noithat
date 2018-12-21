<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'config';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'point'
    ];
    protected $hidden= ['id'];

}
