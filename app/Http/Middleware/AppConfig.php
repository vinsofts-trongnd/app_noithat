<?php

namespace App\Http\Middleware;
use Request;
use Closure;
use App\Entities\Models\Config;
class AppConfig
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token      = Request::header('Authorization');
        $token      = substr($token, 7, strlen($token));
        $access     = Config::where('access_token',$token)->first();
        if($access == null ){
            return response()->json([
                'code'      => 403,
                'messages'  => 'Dont have permission to access'],403
            );
        }
        return $next($request);
    }
}
