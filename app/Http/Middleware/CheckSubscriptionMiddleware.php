<?php

namespace App\Http\Middleware;

use App\Models\Config;
use App\Models\Subscriptions;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!is_null(Auth::user())){
            $data = Subscriptions::query()->where('company_id', Auth::id())->where('valid_until', '>', Carbon::now())->first();
            if(!is_null($data)){
                $request['is_subscribed'] = 1;
//                $request->valid_until_date =
                dd($data);
            }else{
                if(Config::query()->find(1)->first()['free_subscription'] != 0){
                    $request['is_subscribed'] = 1;
                }else{
                    $request['is_subscribed'] = 0;
                }
            }
            return $next($request);
        }else{
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
    }
}
