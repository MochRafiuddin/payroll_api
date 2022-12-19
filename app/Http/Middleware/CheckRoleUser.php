<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\Helper;  

class CheckRoleUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$kode_new)
    {
        
        $kode_with_flag = explode("#$#", $kode_new);
        $flag = 0;
        foreach ($kode_with_flag as $key => $kode) {
            $kode_q = $kode;

            if (Helper::can_akses($kode_q)) {
                // return abort(404);
                $flag = 1;
            }
        }
        
        if ($flag == 0) {
            return abort(404);
        }
        return $next($request);
    }
}