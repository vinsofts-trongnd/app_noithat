<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'devvn_quanhuyen';

    protected $primaryKey = 'maqh';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'type',
        'matp'
    ];
}
