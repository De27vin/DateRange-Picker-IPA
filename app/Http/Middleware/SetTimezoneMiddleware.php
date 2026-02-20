<?php
namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use DateTimeZone;
use Illuminate\Http\Request;
 
class SetTimezoneMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // NOT NEEDED - OPERATIONS ON SERVER CAN BE PERFORMED BASED ON UTC - ONLY DISPLAY CAN BE DONE IN USER TIMEZONE
//        if (auth()->check()) {
//            // This sets the default timezone for Carbon and PHP to the users timezone
//            date_default_timezone_set(auth()->user()->user_timezone);
//            // Here we are using php-intl extension to get users locale (at least trying to guess it!)
//            $locale = new DateTimeZone(auth()->user()->user_timezone);
//            $localeCode = $locale->getLocation()['country_code'] ?? 'en_US';
//            // Making sure Carbon knows which locale we will work with
//            Carbon::setLocale($localeCode);
//        }
 
        return $next($request);
    }
}