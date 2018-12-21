<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Models\RightCondition;
use App\Constants\ResponseStatusCode;

class RightConditionController extends Controller
{
    /**
     * API get list right condition
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getListRightCondition()
    {
        $rightConditions = RightCondition::get();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages' => 'Get list right condition successful',
                'rightConditions' => $rightConditions
            ]
        ]);
    }
}
