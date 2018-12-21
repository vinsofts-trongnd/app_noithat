<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $table = 'devvn_xaphuongthitran';

    protected $primaryKey = 'xaid';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'type',
        'maqh'
    ];
}
