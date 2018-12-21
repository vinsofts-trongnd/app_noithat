<?php

namespace App\Http\Controllers;

use App\Constants\ResponseStatusCode;
use Illuminate\Http\Request;
use App\Entities\Models\Service;

class ServiceController extends Controller
{   
    /**
     * API get list service
     *
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function service()
    {
        $services = [];
        Service::get()->map(function($item) use(&$services) {
            $services [] = [
                'id'   => $item->id, 
                'name' => $item->name
            ];        
        });

        return response()->json([
            'code'      => ResponseStatusCode::OK,
            'data'      => [
                'messages'  => 'Get list service successful',
                'service'   => $services
            ]
        ]);
    }
}
