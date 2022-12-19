<?php 
namespace App\Http\Middleware;

use Closure;
use Session;
use App;
use Config;
class LanguageSwitcher
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
        if (!Session::has('locale'))
         {
           Session::put('locale',Config::get('app.locale'));
           // $langlang = \DB::table('m_bahasa')->select('id_bahasa')
           //          ->where('kode_bahasa','=',Config::get('app.locale'))
           //              ->first();

           //      Session::put('id_locale',$langlang->id_bahasa);
        }
        App::setLocale(session('locale'));
        return $next($request);
    }
}
 ?>