<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class KeepPreviousUrl
{
    /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
    public function handle(Request $request, Closure $next)
    {
        if(!Str::contains(url()->previous(), '/livewire/message/') && !Str::contains(url()->current(), '/livewire/message/')){
            if(is_null(session('previousPage')) || url()->current() != url()->previous()){
                session(['previousPage' => url()->previous()]);
            }
        }
        return $next($request);
    }
}