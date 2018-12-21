<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecruitmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'user'                  => $this->user,
            'vacancies'             => $this->vacancies,
            'work_location_name'    => $this->work_location_name,
            'work_location_lat_lng' => $this->work_location_lat_lng,
            'city_name'             => $this->city_name,
            'wage_min'              => $this->wage_min,
            'wage_max'              => $this->wage_max,
            'number_vacancies'      => $this->number_vacancies,
            'type'                  => $this->type,
            'experience'            => $this->experience,
            'support_mode'          => $this->support_mode,
            'time_work'             => $this->time_work,
            'description'           => $this->description,
            'level_recruitment'     => $this->levels,
            'probationary_period'   => $this->probationary_period,
            'videos'                => $this->videos,
            'images'                => json_decode($this->images),
            'status_notice'         => $this->status_notice,
            'service'               => $this->services,
            'created_at'            => $this->created_at->toDateTimeString(),
            'updated_at'            => $this->updated_at->toDateTimeString(),
        ];
    }
}
