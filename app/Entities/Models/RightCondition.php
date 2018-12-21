<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class RightCondition extends Model
{
    protected $table = 'right_conditions';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'rate',
        'rights',
        'conditions'
    ];
}
