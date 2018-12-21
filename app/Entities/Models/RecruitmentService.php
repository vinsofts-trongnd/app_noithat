<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentService extends Model
{
    protected  $table ='recruitment_service';

    protected $fillable = [
        'service_id',
        'recruitment_id',
    ];
    public $timestamps = false;
}
