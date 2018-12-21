<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class DateCall extends Model
{
    protected $table='date_call';
    protected $fillable=[
        'date',
        'recruitment_id',
        'profile_id',
        'user_id',
        'user_receive',
        'status_notice'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_receive', 'id_users');
    }

    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class, 'recruitment_id', 'id');
    }
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id');
    }
}
