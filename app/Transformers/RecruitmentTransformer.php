<?php

namespace App\Transformers;

use App\Constants\Setting;
use App\Entities\Models\SaveJob;
use League\Fractal\TransformerAbstract;
use App\Entities\Models\Recruitment;
use App\Services\Averagestar\AveragestarService;

class RecruitmentTransformer extends TransformerAbstract
{
    /**
     * @var bool
     */
    private $user_login_id;

    /**
     * RecruitmentTransformer constructor.
     *
     * @param $user_login_id
     */
    public function __construct($user_login_id = 0)
    {
        $this->user_login_id = $user_login_id;
    }

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Recruitment $recruitment)
    {
        $result = [
            'id'                    => $recruitment->id,
            'user'                  => $recruitment->user,
            'vacancies'             => $recruitment->vacancies,
            'work_location_name'    => $recruitment->work_location_name,
            'work_location_lat_lng' => $recruitment->work_location_lat_lng,
            'city_name'             => $recruitment->city_name,
            'wage_min'              => $recruitment->wage_min,
            'wage_max'              => $recruitment->wage_max,
            'number_vacancies'      => $recruitment->number_vacancies,
            'type'                  => $recruitment->type,
            'experience'            => $recruitment->experience,
            'support_mode'          => $recruitment->support_mode,
            'time_work'             => $recruitment->time_work,
            'description'           => $recruitment->description,
            'level_recruitment'     => $recruitment->levels,
            'probationary_period'   => $recruitment->probationary_period,
            'videos'                => $recruitment->videos,
            'images'                => json_decode($recruitment->images),
            'status_notice'         => $recruitment->status_notice,
            'service'               => $recruitment->services,
            'km'                    => $recruitment->km,
            'updated_at'            => $recruitment->updated_at->toDateTimeString(),
            'created_at'            => $recruitment->created_at->toDateTimeString(),
        ];

        if (isset($recruitment->user) && $recruitment->user) {
            $output = AveragestarService::rating($recruitment->user->id_users);
            $recruitment->user->rate_feedback = $output->result;
        }
        if (isset($recruitment->user) && $recruitment->user) {
            $recruitment->user->count_comment = $output->count_comment;
        }

        if ($this->user_login_id) {
            $savedJob = SaveJob::where('user_id', $this->user_login_id)->where('saved_job_id',
                $recruitment->id)->first();

            if ($savedJob) {
                $result['save_status'] = Setting::SAVED;
                return $result;
            }
            $result['save_status'] = Setting::UNSAVED;
        }

        return $result;

    }
}
