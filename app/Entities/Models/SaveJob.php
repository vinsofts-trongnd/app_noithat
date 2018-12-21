<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class SaveJob extends Model
{
    protected $table = 'saved_job';
    protected $fillable = [
        'user_id',
        'saved_job_id',
        'status'
    ];
    public $timestamps = false;

    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class, 'saved_job_id', 'id')
            ->with('service','level_recruitment','experience','user');
    }
}
