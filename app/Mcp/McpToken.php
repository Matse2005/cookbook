<?php

namespace App\Mcp;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Spike version: the token lives in a file under storage so the main server and
 * the MCP listener (two PHP processes) read the same value. Step 6.2 moves it
 * into the settings table.
 */
class McpToken
{
    public function value(): string
    {
        $configured = config('cookbook.mcp_token');

        if (is_string($configured)) {
            return $configured;
        }

        $path = storage_path('app/private/mcp-token');

        if (! File::exists($path)) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, Str::random(48));
            @chmod($path, 0600);
        }

        return trim(File::get($path));
    }
}
