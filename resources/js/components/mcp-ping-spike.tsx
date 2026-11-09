import { useEffect, useState } from 'react';

type NativeBridge = {
    on: (event: string, callback: (payload: { at: string }) => void) => void;
};

declare global {
    interface Window {
        Native?: NativeBridge;
    }
}

const EVENT = 'App\\Events\\McpPinged';

// Spike for Step 1.3: shows that an event dispatched during an MCP request reaches the open window.
export function McpPingSpike() {
    const [lastPing, setLastPing] = useState<string | null>(null);

    useEffect(() => {
        const listen = () =>
            window.Native?.on(EVENT, (payload) => setLastPing(payload.at));

        if (window.Native) {
            listen();
        } else {
            window.addEventListener('native:init', listen, { once: true });
        }
    }, []);

    return (
        <p data-testid="mcp-ping" className="mb-4 text-sm">
            Last MCP ping: {lastPing ?? 'none yet'}
        </p>
    );
}
