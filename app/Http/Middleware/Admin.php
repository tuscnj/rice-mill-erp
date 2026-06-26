<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is logged in AND has the admin role
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // If they are not an admin, kick them back to the dashboard with an error
        return redirect('/')->with('error', 'Access Denied: Administrator clearance required.');
    }
}