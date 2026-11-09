<?php

namespace App\Http\Middleware;

use App\Mcp\McpToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMcpToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = app(McpToken::class)->value();
        $given = $request->bearerToken();

        if ($expected === '' || ! is_string($given) || ! hash_equals($expected, $given)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
