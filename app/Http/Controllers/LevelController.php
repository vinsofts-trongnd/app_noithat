<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Models\Level;
use App\Constants\ResponseStatusCode;

class LevelController extends Controller
{
    /**
     * API get list level
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function getListLevel()
    {
        $levels = Level::get();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => [
                'messages' => 'Get list level successful',
                'levels'   => $levels,
            ],
        ], ResponseStatusCode::OK);
    }
}
