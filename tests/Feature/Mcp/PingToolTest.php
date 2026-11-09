<?php

$callPing = [
    'jsonrpc' => '2.0',
    'id' => 1,
    'method' => 'tools/call',
    'params' => ['name' => 'ping', 'arguments' => []],
];

beforeEach(function () {
    config(['cookbook.mcp_token' => 'test-token']);
});

it('rejects a call without a token', function () use ($callPing) {
    $this->postJson('/mcp', $callPing)->assertUnauthorized();
});

it('rejects a call with the wrong token', function () use ($callPing) {
    $this->postJson('/mcp', $callPing, ['Authorization' => 'Bearer wrong'])
        ->assertUnauthorized();
});

it('rejects every call when no token is configured', function () use ($callPing) {
    config(['cookbook.mcp_token' => null]);

    $this->postJson('/mcp', $callPing, ['Authorization' => 'Bearer '])
        ->assertUnauthorized();
});

it('answers ping with the token', function () use ($callPing) {
    $this->postJson('/mcp', $callPing, ['Authorization' => 'Bearer test-token'])
        ->assertOk()
        ->assertJsonPath('result.content.0.text', 'pong');
});
