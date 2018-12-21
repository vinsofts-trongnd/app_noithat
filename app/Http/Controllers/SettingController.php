<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Entities\Models\Config;
use App\Constants\ResponseStatusCode;

class SettingController extends Controller
{

    /**
     * API GET CONFIG
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfig()
    {
        $config = Config::first();

        return response()->json([
            'code' => ResponseStatusCode::OK,
            'data' => $config,
        ]);
    }
}
