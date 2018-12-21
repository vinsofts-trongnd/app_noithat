<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class SaveProfile extends Model
{
    protected $table = 'profile_saved';
    protected $fillable = [
        'user_id',
        'profile_id',
    ];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_users');
    }

    public function profiles()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id')->with('experience','level_current','level_desired','services','ability_overtime');
    }
}
