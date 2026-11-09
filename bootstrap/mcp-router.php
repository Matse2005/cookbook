<?php

/*
 * Router for the MCP listener: `php -S 127.0.0.1:<port> bootstrap/mcp-router.php`.
 *
 * NativePHP's PreventRegularBrowserAccess middleware answers 403 to any request
 * without its per-launch secret, which Claude Code cannot know. This listener only
 * serves /mcp, where the bearer token middleware does the authentication, so it
 * adds the secret from its own environment and refuses every other path.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path !== '/mcp') {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'Not found';

    return true;
}

$secret = getenv('NATIVEPHP_SECRET');

if (is_string($secret) && $secret !== '') {
    $_SERVER['HTTP_X_NATIVEPHP_SECRET'] = $secret;
}

require __DIR__.'/../public/index.php';
