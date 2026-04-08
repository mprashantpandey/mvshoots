<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMainAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('admin') ?? $request->user();

        if (! $user instanceof Admin || ! $user->isMainAdmin()) {
            abort(403, 'Only the main platform administrator can manage staff accounts.');
        }

        return $next($request);
    }
}
