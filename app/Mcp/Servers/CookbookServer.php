<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\PingTool;
use App\Mcp\Tools\SpikeGitBranchTool;
use App\Mcp\Tools\WipeSpikeTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Cookbook')]
#[Version('0.0.1')]
#[Instructions('Tools of the Cookbook macOS app.')]
class CookbookServer extends Server
{
    protected array $tools = [
        PingTool::class,
        WipeSpikeTool::class,
        SpikeGitBranchTool::class,
    ];
}
