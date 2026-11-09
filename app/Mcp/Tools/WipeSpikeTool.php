<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('wipe')]
#[Description('Spike tool marked destructive. It changes nothing.')]
#[IsDestructive]
class WipeSpikeTool extends Tool
{
    public function handle(Request $request): Response
    {
        return Response::text('nothing was wiped');
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
