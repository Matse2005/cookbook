<?php

namespace App\Providers;

use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\ChildProcess;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Window::open();

        // NativePHP's own server runs on a random port, so MCP gets a second listener on a fixed one.
        ChildProcess::php(
            ['-S', '127.0.0.1:'.config('cookbook.mcp_port'), base_path('bootstrap/mcp-router.php')],
            alias: 'mcp',
            persistent: true,
        );
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
