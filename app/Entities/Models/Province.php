<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'devvn_tinhthanhpho';

    protected $primaryKey = 'matp';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'type'
    ];
}
