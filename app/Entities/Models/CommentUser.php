<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class CommentUser extends Model
{
    protected $table='comments_user';

    protected $fillable=[
        'contents',
        'user_send',
        'rate',
        'user_receive',
        'profile_id',
        'recruitment_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_send', 'id_users');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id');
    }

    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class, 'recruitment_id', 'id');
    }
}
