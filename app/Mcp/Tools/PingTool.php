<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('ping')]
#[Description('Check that the Cookbook app is running. Returns "pong".')]
class PingTool extends Tool
{
    public function handle(Request $request): Response
    {
        return Response::text('pong');
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
