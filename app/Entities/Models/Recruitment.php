<?php

namespace App\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class Recruitment extends Model
{
    protected $table = 'recruitment';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'vacancies',
        'work_location_name',
        'work_location_lat_lng',
        'city_name',
        'wage_min',
        'wage_max',
        'number_vacancies',
        'type',
        'experience_id',
        'support_mode',
        'time_work',
        'description',
        'level_recruitment_id',
        'probationary_period',
        'images',
        'videos',
        'status_notice',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_users');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'recruitment_service', 'recruitment_id', 'service_id');
    }

    public function recruitment_services()
    {
        return $this->hasMany(RecruitmentService::class, 'recruitment_id', 'id');
    }

    public function experience()
    {
        return $this->belongsTo(Experience::class, 'experience_id', 'id');
    }

    public function levels()
    {
        return $this->belongsTo(Level::class,'level_recruitment_id','id');
    }

    public function level_recruitment()
    {
        return $this->belongsTo(Level::class,'level_recruitment_id','id');
    }

    public function service()
    {
        return $this->belongsToMany(Service::class, 'recruitment_service', 'recruitment_id', 'service_id');
    }
}
