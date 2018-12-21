<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Averagestar\AveragestarService;

class UserResource extends JsonResource
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
        $result = [
            'id_users'       => $this->id_users,
            'loaitk'         => $this->loaitk,
            'vitien'         => $this->vitien,
            'name'           => $this->name,
            'username'       => $this->username,
            'email'          => $this->email,
            'telephone'      => $this->telephone,
            'lastvisit'      => $this->lastvisit,
            'active'         => $this->active,
            'super'          => $this->super,
            'address'        => $this->address,
            'address_t'      => $this->address_t,
            'address_h'      => $this->address_h,
            'address_x'      => $this->address_x,
            'dc_nhaxuong'    => $this->dc_nhaxuong,
            'dc_vanphong'    => $this->dc_vanphong,
            'yahoo'          => $this->yahoo,
            'linkfb'         => $this->linkfb,
            'linktw'         => $this->linktw,
            'image'          => $this->image,
            'avatar'         => $this->avatar,
            'cover'          => $this->cover,
            'showed'         => $this->showed,
            'linhvucsanxuat' => $this->linhvucsanxuat,
            'filebaogia'     => $this->filebaogia,
            'idFB'           => $this->idFB,
            'idGG'           => $this->idGG,
            'code_intro'     => $this->code_intro,
            'code_present'   => $this->code_present,
            'service'        => $this->service,
            'identity_card'  => $this->identity_card,
            'type_temp'      => $this->type_temp,
            'point'          => $this->point,
            'rate'           => $this->rate,
            'gender'         => $this->gender,
            'date_of_birth'  => $this->date_of_birth,
        ];

        if (isset($this->id_users) && $this->id_users) {
            $output = AveragestarService::rating($this->id_users);
            $result['rate_feedback'] = $output->result;
        }
        if (isset($this->id_users) && $this->id_users) {
            $result['count_comment'] = $output->count_comment;
        }

        return $result;
    }
}
