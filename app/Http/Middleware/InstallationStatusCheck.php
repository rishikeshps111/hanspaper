<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallationStatusCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // env('INSTALLATION_STATUS')
        $installationStatus = (bool) config('app.installation_status') === false;

        if ($installationStatus) {
            return redirect('/install');
        }

        return $next($request);
    }


}
