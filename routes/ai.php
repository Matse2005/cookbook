<?php

use App\Http\Middleware\RequireMcpToken;
use App\Mcp\Servers\CookbookServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', CookbookServer::class)->middleware(RequireMcpToken::class);
