# Spike results

Results of the Milestone 1 spikes. Each section says what was tried, what happened, the evidence and the decision. Tokens appear as `<token>`, never as real values.

## NativePHP and laravel/mcp versions

Checked on 2026-11-09. Package facts come from `composer show -a <package>` against Packagist; documentation facts come from the NativePHP 2.x docs at https://nativephp.com/docs/desktop/2/.

### Packages

- `nativephp/desktop` latest is 2.4.0. It requires php ^8.3, illuminate/contracts ^10.0 to ^13.0 and ext-zip (`composer show -a nativephp/desktop`). Installed with `composer require nativephp/desktop`, then `php artisan native:install`.
- `nativephp/electron` (latest 1.3.0) is abandoned with `nativephp/desktop` as its replacement, per `https://packagist.org/packages/nativephp/electron.json` (`"abandoned": "NativePHP/desktop"`). The upgrade guide (https://nativephp.com/docs/desktop/2/getting-started/upgrade-guide) says to replace the package and that the root namespace is now `Native\Desktop`.
- `laravel/mcp` latest is v1.1.2. It requires php ^8.2, illuminate packages ^11.45.3, ^12.41.1 or ^13.0, and illuminate/json-schema ^12.41.1 or ^13.0 (`composer show -a laravel/mcp`).
- `laravel/framework` latest is v13.40.0. The scaffold in Step 1.2 uses Laravel 13, the major the Laravel installer 5.32.0 installs by default (confirmed in Step 1.2 from `composer.lock`).
- The Laravel installer 5.32.0 has a `--no-authentication` flag next to `--react` and `--pest` (`laravel new --help`), so the starter kit can be created without Fortify instead of removing it afterwards.

### Commands

- `php artisan native:run` is current. The commands page (https://nativephp.com/docs/desktop/2/getting-started/commands) says `native:serve` still exists but is deprecated in favour of `native:run`.
- `php artisan native:build {os?} {arch?}` with os `all`, `linux`, `mac` or `win` and arch `x64` or `arm64`, so `native:build mac arm64` builds for Apple Silicon (same page).

### Launch at login

Documented on https://nativephp.com/docs/desktop/2/the-basics/application under "Open at login": `App::openAtLogin(true)` enables it, `App::openAtLogin(false)` disables it and `App::openAtLogin()` reads the state, on macOS and Windows only. The facade is `Native\Desktop\Facades\App`. There is no config key for it.

### Menu bar and staying alive without a window

https://nativephp.com/docs/desktop/2/the-basics/menu-bar documents `MenuBar::create()` in `NativeAppServiceProvider::boot()`. Adding a menu bar hides the dock icon unless `->showDockIcon()` is called. A context menu is set with `->withContextMenu(Menu::make(...))`, and `MenuBar::onlyShowContextMenu()` shows only that menu on click. https://nativephp.com/docs/desktop/2/the-basics/application-menu documents `Menu::quit()` and `Menu::label('Open')->event(SomeEvent::class)`, where the event extends `MenuItemClicked` and a listener can call `Window::open()`.

Whether the app keeps running after its last window closes is not documented. The application, windows, menu bar and configuration pages mention no setting for it; the windows page only documents a `WindowClosed` event. Step 1.4 tests it.

### Pushing an event from PHP to the open window

https://nativephp.com/docs/desktop/2/digging-deeper/broadcasting: an event that implements `ShouldBroadcastNow` and returns `[new Channel('nativephp')]` from `broadcastOn()` reaches every window. NativePHP injects `window.Native`; the renderer listens with `Native.on('Fully\\Qualified\\EventClass', (payload, event) => {})` inside a `native:init` listener. No Echo or Reverb is involved. Step 1.3 tests whether this works when the event is dispatched from a request that arrives on the second listener rather than from NativePHP's own server.

### Child processes

https://nativephp.com/docs/desktop/2/digging-deeper/child-processes documents `ChildProcess::start(cmd:, alias:, cwd:, env:, persistent:)`, plus `::php()`, `::artisan()`, `::node()`, `::get()`, `::all()`, `::stop()`, `::restart()` and `::message()`. A process started with `persistent: true` is booted again if it crashes. The page says the processes are shut down gracefully when the app quits. The working directory defaults to the application's, and processes inherit the application's environment plus `env:`. Output arrives as `MessageReceived` and `ErrorReceived` events. Step 1.3 uses this for the MCP listener on port 47123.

### App version and app id

https://nativephp.com/docs/desktop/2/getting-started/configuration shows the published `config/nativephp.php` with `'version' => env('NATIVEPHP_APP_VERSION', '1.0.0')` and `'app_id' => env('NATIVEPHP_APP_ID', 'com.nativephp.app')`. The version decides whether the app needs an update, and the building page says migrations run on users' machines only when it changes. Steps 8.1 and 8.3 use `nativephp.version`, set through `NATIVEPHP_APP_VERSION`.

### Building requirements

The installation page lists PHP 8.3+, Laravel 11+, Node 22+ and macOS 12+. It does not say whether Xcode or the Command Line Tools are required. Signing and notarization read `NATIVEPHP_APPLE_ID`, `NATIVEPHP_APPLE_ID_PASS` and `NATIVEPHP_APPLE_TEAM_ID` (https://nativephp.com/docs/desktop/2/publishing/building). The docs do not say whether a build without those credentials skips signing or fails, and they have no GitHub Actions example. Step 1.5 finds out.
