<?php

namespace App\Http\Controllers;

use App\Entities\Models\District;
use Illuminate\Http\Request;
use App\Entities\Models\Province;
use App\Entities\Models\Ward;
use App\Constants\ResponseStatusCode;

class AddressController extends Controller
{
    /**
     * API get list province
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getListProvince()
    {
        $provinces = Province::get();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages' => 'Get list province successful',
                'provinces' => $provinces
            ]
        ]);
    }

    /**
     * API get list district
     *
     *  @param $province_id
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getListDistrict($province_id)
    {
        $districts = District::where('matp', $province_id)->get();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages' => 'Get list district successful',
                'districts' => $districts
            ]
        ]);
    }

    /**
     * API get list
     *
     *  @param $district_id
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getListWard($district_id)
    {
        $wards = Ward::where('maqh', $district_id)->get();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages' => 'list ward successful',
                'districts' => $wards
            ]
        ]);
    }
}
