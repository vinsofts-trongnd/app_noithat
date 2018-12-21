<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected  $table ='profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',
        'experience_id',
        'position',
        'current_level_id',
        'desired_level_id',
        'current_salary',
        'desired_salary',
        'lat_lng_current',
        'address_current',
        'lat_lng_desired',
        'address_desired',
        'skill',
        'ability_overtime_id',
        'ability_manage',
        'ability_workfar',
        'image_product',
        'image_forte',
        'image_drawing',
        'status_notice',
        'city_current',
        'city_desired',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_users');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'profiles_service', 'profile_id', 'service_id');
    }

    public function profile_services()
    {
        return $this->hasMany(ProfileService::class, 'profile_id', 'id');
    }

    public function experience()
    {
        return $this->belongsTo(Experience::class, 'experience_id', 'id');
    }

    public function level_current()
    {
        return $this->belongsTo(Level::class,'current_level_id','id');
    }

    public function level_desired()
    {
        return $this->belongsTo(Level::class,'desired_level_id','id');
    }

    public function overtime(){
        return $this->belongsTo(Overtime::class,'ability_overtime_id','id');
    }

    public function ability_overtime(){
        return $this->belongsTo(Overtime::class,'ability_overtime_id','id');
    }
}
