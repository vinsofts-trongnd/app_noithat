<?php

namespace App\Http\Resources;

use App\Services\Averagestar\AveragestarService;
use Illuminate\Http\Resources\Json\JsonResource;
use JWTAuth;
use App\Constants\Setting;
use App\Entities\Models\SaveProfile;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $user_login = JWTAuth::parseToken()->authenticate();

        $result = [
            'id'               => $this->id,
            'user'             => $this->user,
            'type'             => $this->type,
            'experience'       => $this->experience,
            'position'         => $this->position,
            'current_level'    => $this->level_current,
            'desired_level'    => $this->level_desired,
            'current_salary'   => $this->current_salary,
            'desired_salary'   => $this->desired_salary,
            'lat_lng_current'  => $this->lat_lng_current,
            'address_current'  => $this->address_current,
            'city_current'     => $this->city_current,
            'lat_lng_desired'  => $this->lat_lng_desired,
            'address_desired'  => $this->address_desired,
            'city_desired'     => $this->city_desired,
            'skill'            => $this->skill,
            'image_product'    => json_decode($this->image_product),
            'image_forte'      => json_decode($this->image_forte),
            'image_drawing'    => json_decode($this->image_drawing),
            'ability_overtime' => $this->overtime,
            'ability_manage'   => $this->ability_manage,
            'ability_workfar'  => $this->ability_workfar,
            'service'          => $this->services,
            'km'               => $this->km,
            'created_at'       => $this->created_at->toDateTimeString(),
            'updated_at'       => $this->updated_at->toDateTimeString(),
        ];

        if (isset($this->user) && $this->user) {
            $output = AveragestarService::rating($this->user->id_users);
            $this->user->rate_feedback = $output->result;
        }
        if (isset($this->user) && $this->user) {
            $this->user->count_comment = $output->count_comment;
        }

        $saveProfile = SaveProfile::where('user_id', $user_login->id_users)->where('profile_id',$this->id)->first();
        $this['image_product'] = json_decode($this->image_product);
        $this['image_forte']   = json_decode($this->image_forte);
        $this['image_drawing'] = json_decode($this->image_drawing);

        if ($saveProfile) {
            $result['save_status'] = Setting::SAVED;
        } else {
            $result['save_status'] = Setting::UNSAVED;
        }

        return $result;
    }
}
