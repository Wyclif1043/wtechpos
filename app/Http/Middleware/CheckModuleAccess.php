<?php
// app/Http/Middleware/CheckModuleAccess.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, $module): Response
    {
        if (!$request->user() || !$request->user()->canAccessModule($module)) {
            abort(403, 'Unauthorized access to module: ' . $module);
        }

        return $next($request);
    }
}