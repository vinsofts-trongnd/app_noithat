<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Models\Overtime;
use App\Constants\ResponseStatusCode;

class OvertimeController extends Controller
{

    /**
     * Get ability overtime.
     *
     * @param  id_profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function overTime()
    {
        $overtime = Overtime::get();
        return response()->json([
            'code'     => ResponseStatusCode::OK,
            'overtime' => $overtime,
        ]);
    }
}
