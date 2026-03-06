<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceAcceptsJson
{
    private const string EXPECTED_ACCEPT_HEADER = 'application/json';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('Accept') !== self::EXPECTED_ACCEPT_HEADER) {
            $request->headers->set('Accept', self::EXPECTED_ACCEPT_HEADER);
        }

        return $next($request);
    }
}
