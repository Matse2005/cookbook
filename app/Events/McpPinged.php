<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class McpPinged implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(public string $at) {}

    /**
     * NativePHP forwards events on the "nativephp" channel to every open window.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('nativephp')];
    }
}
