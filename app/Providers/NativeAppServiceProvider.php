<?php

namespace App\Providers;

use App\Events\OpenWindowClicked;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\App;
use Native\Desktop\Facades\ChildProcess;
use Native\Desktop\Facades\Menu;
use Native\Desktop\Facades\MenuBar;
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

        // The app keeps running from the menu bar after its window closes, because the MCP server lives in it.
        MenuBar::create()
            ->onlyShowContextMenu()
            ->showDockIcon()
            ->withContextMenu(Menu::make(
                Menu::label('Open Cookbook')->event(OpenWindowClicked::class),
                Menu::separator(),
                Menu::quit('Quit Cookbook'),
            ));

        // Only the installed app registers itself; in development this would add the dev Electron binary to the login items.
        if (app()->isProduction()) {
            App::openAtLogin(true);
        }

        // NativePHP's own server runs on a random port, so MCP gets a second listener on a fixed one.
        ChildProcess::php(
            ['-S', '127.0.0.1:'.config('cookbook.mcp_port'), base_path('bootstrap/mcp-router.php')],
            alias: 'mcp',
            persistent: true,
        );
    }

    /**
     * Return an array of php.ini directives to be set.
     *
     * @return array<string, string>
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
