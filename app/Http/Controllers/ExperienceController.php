<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Models\Experience;
use App\Constants\ResponseStatusCode;

class ExperienceController extends Controller
{
    /**
     * API get list experience
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getListExperience()
    {
        $experiences = Experience::get();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages'    => 'Get list experience successful',
                'experiences' => $experiences,
            ],
        ]);
    }
}
