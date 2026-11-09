# Spike results

Results of the Milestone 1 spikes. Each section says what was tried, what happened, the evidence and the decision. Tokens appear as `<token>`, never as real values.

Spike code removed in Step 1.7 (commit `0f32e72`): the `wipe` and `spike_git_branch` tools, `McpPinged`, the welcome page ping line and `.github/workflows/spike-dmg.yml`. The `ping` tool, its server, the token middleware, the router and the child process stay until Step 6.2 replaces `ping`. Sections below describe the code as it was during each spike.

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

## Starter kit fit

Spec risk 3. Tried on 2026-11-09 with Laravel installer 5.32.0.

### What was built

`laravel new cookbook --react --pest --no-authentication --no-boost --database=sqlite --npm -n`, moved into the repository root. The `--no-authentication` flag gives the React kit without Fortify, auth pages or auth routes, so nothing had to be removed by hand. The resolved framework is Laravel v13.40.0 (`composer.lock`). Laravel Boost was left out because the spec does not name it.

Two differences from the plan, both decided by the user on 2026-11-09:

- Both React kits (with and without auth) now use Vite+ (`vite-plus` 1.4.0) as the JS toolchain: `vp build`, `vp test` (Vitest 5.0.5), `vp lint` (oxlint 1.89.0), `vp fmt` (oxfmt 0.74.0) and `vp check` for all three, per `npx vp toolchain`. There is no ESLint. Decision: follow Vite+ and do not add ESLint. Vitest settings live in the `test` block of `vite.config.ts` as the Vite+ docs recommend (`node_modules/vite-plus/docs/guide/test.md`), so there is no `vitest.config.ts`, and tests import from `vite-plus/test`.
- The kit without auth ships no shadcn/ui (no `components.json`, no components). The full kit has 26 shadcn components but also Fortify, passkeys and two-factor auth. Decision: keep the no-auth kit and run `npx shadcn@latest init -t laravel -b radix -p nova`. That wrote `components.json` (style `radix-nova`, base colour neutral, lucide icons), `resources/js/components/ui/button.tsx` and theme variables in `resources/css/app.css`, and added `radix-ui`, `class-variance-authority`, `lucide-react`, `tw-animate-css`, `@fontsource-variable/geist`, `cn` (shadcn's class merge package, repository `shadcn-ui/cn`) and `shadcn`. Nova is the CLI's default preset; I picked it because the init prompts for one.

NativePHP: `composer require nativephp/desktop` resolved `^2.4`, then `php artisan native:install -n` downloaded the PHP 8.4 binary for mac-arm64, installed Electron under `vendor/nativephp/desktop/resources/electron`, published `config/nativephp.php` and `app/Providers/NativeAppServiceProvider.php`, and added the `native:dev` composer script and a `native:install --force --quiet` post-update hook.

Test tooling: `@testing-library/react`, `@testing-library/dom` and `jsdom` (jsdom approved by the user) as dev dependencies, with `resources/js/smoke.test.tsx` rendering the shadcn button.

### Problems found

- The kit ships a `pnpm-workspace.yaml`. The shadcn CLI saw it and installed with pnpm, writing a `pnpm-lock.yaml` and leaving `package-lock.json` without the new packages. I deleted both pnpm files and reran `npm install`, so npm is the only package manager.
- The kit's `.gitignore` ignores `/.claude`. I kept the repository's own `.claude/skill-workspace/` rule instead of the blanket one, so `.claude/rules/` can be committed later. The kit's nested `.gitignore` files (`database/`, `bootstrap/cache/`, `storage/...`) are kept as generated.
- shadcn's `components.json` and `app.css` and NativePHP's provider stub failed the formatters as generated; `vp fmt` and `pint` fixed them.
- The root view loads Instrument Sans through `laravel-vite-plugin/fonts` with `bunny(...)`, a remote font service. Step 4.10 replaces it.

### Result

All of these pass on the clean scaffold: `./vendor/bin/pest` (2 tests), `npx vp test` (1 test), `npx vp check` (format and lint), `npx tsc --noEmit`, `./vendor/bin/pint --test` and `npm run build`.

`php artisan native:run --no-interaction` started Electron from `vendor/nativephp/desktop/resources/electron` and PHP's server on `127.0.0.1:8100` (`lsof -iTCP -sTCP:LISTEN`). The user confirmed the window showed the starter welcome page. No screenshot is committed: `screencapture -l <window id>` failed with "could not create image from window" because the terminal has no screen recording permission.

Decision: the starter kit and NativePHP fit together. The fallback (plain Laravel with Inertia added by hand) is not needed.

## MCP in NativePHP

Spec risk 1, plus the push question. Tried on 2026-11-09 with `laravel/mcp` v1.1.2 and `nativephp/desktop` 2.4.0, in `php artisan native:run`.

### What was built

- `laravel/mcp` registers `Mcp::web('/mcp', CookbookServer::class)` in `routes/ai.php` behind `App\Http\Middleware\RequireMcpToken`, which compares the bearer token with `hash_equals` and answers 401 otherwise.
- Two spike tools: `ping` (returns `pong` and dispatches `App\Events\McpPinged`) and `wipe` (annotated `#[IsDestructive]`, changes nothing).
- The token comes from `config('cookbook.mcp_token')` when set, else from `storage/app/private/mcp-token`, created with 48 random characters on first use. A file is used because two PHP processes need the same value; Step 6.2 moves it to the settings table.
- The second listener is a NativePHP child process started in `NativeAppServiceProvider::boot()`: `ChildProcess::php(['-S', '127.0.0.1:47123', base_path('bootstrap/mcp-router.php')], alias: 'mcp', persistent: true)`. The port comes from `config('cookbook.mcp_port')` (`COOKBOOK_MCP_PORT`, default 47123). The router script answers 404 for every path except `/mcp`, sets the `X-NativePHP-Secret` header from its own `NATIVEPHP_SECRET` environment variable and hands the request to `public/index.php`. NativePHP passes `NATIVEPHP_RUNNING`, `NATIVEPHP_SECRET` and `NATIVEPHP_API_URL` to PHP child processes (`electron-plugin/src/server/api/childProcess.ts` `startPhpProcess`, `server/php.ts` `getDefaultEnvironmentVariables`).
- Kept test: `tests/Feature/Mcp/PingToolTest.php` (no token, wrong token and an empty token get 401; the right token gets `pong`).

### Evidence

`lsof -nP -iTCP:47123 -sTCP:LISTEN` while the app ran:

```
php     54587 matsevh    5u  IPv4 ...  TCP 127.0.0.1:47123 (LISTEN)
```

NativePHP's own server listened on `127.0.0.1:8100` in the same run. Calls with curl:

```
POST /mcp without token        -> 401 {"error":"Unauthorized"}
POST /mcp with Bearer <token>  -> 200 {"jsonrpc":"2.0","id":1,"result":{"resultType":"complete","content":[{"type":"text","text":"pong"}],...}}
GET /                          -> 404 Not found
POST to the en0 address:47123  -> connection refused
```

Registered with `claude mcp add --transport http cookbook-spike http://127.0.0.1:47123/mcp --header "Authorization: Bearer <token>"`, local scope for this project; `claude mcp list` reported `cookbook-spike: http://127.0.0.1:47123/mcp (HTTP) - ✔ Connected`. A headless Claude Code session, `claude -p '...call the ping tool...' --allowedTools 'mcp__cookbook-spike__ping'`, returned `pong` with no permission denials.

### Destructive tool behaviour

- `claude -p '...call the wipe tool...'` without an allow rule: the call was denied (`permission_denials: ['mcp__cookbook-spike__wipe']`). In an interactive session this should be a permission prompt (not tested).
- The same with `--allowedTools 'mcp__cookbook-spike'`, which allows every tool on the server: `wipe` ran and returned `nothing was wiped`, with no denial.

So Claude Code asks before `wipe` the way it asks before any MCP tool that is not allowed, and the `destructiveHint` annotation adds no confirmation of its own. Once a user allows the whole server, `remove_project` and `reset_progress` would run without a prompt. That is the case the plan's revisit names: Step 6.1 would add a required `confirm: true` argument to both tools. Decision for the Step 1.7 gate.

### Push to the window

`McpPinged` implements `ShouldBroadcastNow` on `new Channel('nativephp')`. NativePHP's `Native\Desktop\Events\EventWatcher` posts such events to the Electron API, and the child process has the API URL and secret, so a ping arriving on port 47123 reaches the window. `resources/js/components/mcp-ping-spike.tsx` listens with `window.Native.on('App\\Events\\McpPinged', ...)` after `native:init`. After a ping at 17:40:48 UTC the user saw the line on the welcome page change to that time without a reload. Step 6.9 can use broadcasting on the `nativephp` channel; the change counter fallback is not needed.

### Unexpected finding: the 403 guard does not run

The plan assumed NativePHP's `PreventRegularBrowserAccess` answers 403 without the secret. In this app it never runs. `POST http://127.0.0.1:8100/mcp` with the token and without the NativePHP secret returned 200, and `GET /` on port 8100 returned 200. Booting the app with `NATIVEPHP_RUNNING=true` and checking the HTTP kernel gave `hasMiddleware(PreventRegularBrowserAccess::class) === false`.

The cause: `NativeServiceProvider::packageRegistered()` (`vendor/nativephp/desktop/src/NativeServiceProvider.php:64`, push at `:109`) pushes the middleware onto `$this->app->make(Illuminate\Foundation\Http\Kernel::class)`, but the app binds the singleton to the contract `Illuminate\Contracts\Http\Kernel` (`ApplicationBuilder::withKernels()`, `vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php:57-61`), and `make()` of the concrete class builds a fresh instance every time (`===` is false). The guard is added to a kernel that serves no requests. Verified on Laravel 13; other versions that use `bootstrap/app.php` probably behave the same, but that was not checked.

Consequences: any local process can reach NativePHP's own server on its random port without the secret. That is upstream behaviour and outside this plan's scope. The second listener is still needed, because the main port is random. Setting the secret header in `mcp-router.php` is harmless now and keeps working if NativePHP fixes the guard. Whether the packaged build behaves the same is checked in Step 1.5.

### Other notes

- `php -S` writes a line per request to stderr, which NativePHP rebroadcasts as `ChildProcess\ErrorReceived` and the log broadcaster writes to `laravel.log` on every MCP call. Milestone 6 should quiet this (for example `-q` on `php -S`).
- The dev app's storage is `~/Library/Application Support/laravel-dev/storage`, so the dev token file is there, not in the repository.
- The `cookbook-spike` registration stays in place for Steps 1.4 and 1.5 and is removed at Step 1.7.

Decision: an HTTP MCP server on a fixed localhost port works inside the running app as a NativePHP child process. Milestone 6 keeps the HTTP transport.

## Background running

Second half of spec risk 2. Tried on 2026-11-09 in `php artisan native:run`.

### What was built

`NativeAppServiceProvider::boot()` now creates a menu bar item with `MenuBar::create()->onlyShowContextMenu()->showDockIcon()` and a context menu with "Open Cookbook" (event `App\Events\OpenWindowClicked`, which extends `MenuItemClicked`; the listener `App\Listeners\OpenMainWindow` calls `Window::open()`), a separator and "Quit Cookbook" (`Menu::quit()`). The dock icon stays, because the app also has a normal window. Launch at login is `App::openAtLogin(true)`, called only when `app()->isProduction()`, so development runs do not register the dev Electron binary as a login item.

### Result: closing the window quits the app in development

With the window closed (by the user with the close button, and by `POST <api>/window/close {"id":"main"}` to NativePHP's Electron API with the secret from the PHP process environment), Electron ran its quit path: the run log shows `Process [mcp] stopping`, `PHP server exited` and both child processes exiting, and the MCP listener on 47123 disappeared. It reproduced on every try.

What I ruled out:

- The menu bar code is not the cause: the Step 1.3 provider without any menu bar quits the same way.
- NativePHP's PHP side never calls `App::quit()` (`grep` for `quit()` in `vendor/nativephp/desktop/src` finds only the `App::quit()` definition at `src/App.php:15` and its facade docblock, with no caller).
- The JavaScript `window-all-closed` listeners do not quit. Read from the live main process over the inspector (`V8_INSPECTOR_PORT=9339`, which electron-vite passes to Electron as `--inspect`), there are two: Electron's default `()=>{1===a.listenerCount("window-all-closed")&&a.quit()}`, which only quits when it is the only listener, and NativePHP's, which only quits off macOS.
- With `app.quit` and `app.exit` wrapped at runtime over the inspector, the first call seen was NativePHP's own `app.quit()` inside its `before-quit` handler (`out/main/index.js:2848`), after the quit had started. The quit is therefore started natively, not from JavaScript.

Update after Step 1.5: the cause is how the process is started, not NativePHP. A three-line Electron app (one window, an empty `window-all-closed` handler) started from a shell quits as soon as no window is visible, on Electron 40.10.2 (NativePHP's) and on Electron 45.2.3 alike, also with a hidden extra window, a tray icon, `hide()` instead of `close()`, or `-NSDisableAutomaticTermination YES`. Started through LaunchServices (`open -n Electron.app --args <app>`), the same app stays alive after its window closes. On this machine (macOS 27.0.1), `native:run` starts Electron as a child of a shell, and in every try that made it quit on window close. The packaged app launched with `open` survives the window close and keeps answering MCP (Step 1.5). Why macOS 27 ends a shell-started app without visible windows is not known; it does not affect the installed app.

The earlier note, kept for the record: the cause was not known. The NativePHP docs (https://nativephp.com/docs/desktop/2/getting-started/development) say `native:run` stops when the application quits, but say nothing about closing the window. Decision by the user: park it and test window close on the installed DMG in Step 1.5, where the app runs as a real bundle instead of `electron .`. If the installed app quits too, the fallback the user would decide on is keeping a hidden window alive.

Not verified yet: that the menu bar icon and its menu appear (the user's check ended at the window close), that "Open Cookbook" reopens the window, and launch at login, which only runs in the installed app (Step 1.5).

### Other note

The development storage folder is `~/Library/Application Support/laravel-dev`, named after `APP_NAME=Laravel`, and already held a `browser.log` from 2026-10-02, so it is shared with other NativePHP projects that keep the default name. A temporary debug listener during this spike looped through NativePHP's log forwarding and grew `laravel.log` there to about 4.6 MB; it was removed before the commit. Setting `APP_NAME` and `NATIVEPHP_APP_ID` would give this app its own folder; that belongs to Step 1.5's build.

## Unsigned DMG

Spec risk 4. Tried on 2026-11-09 on macOS 27.0.1 (build 26A434).

### Build on GitHub Actions

`.github/workflows/spike-dmg.yml` runs on `macos-latest` on pushes to `feature/m1-spikes` (GitHub only offers `workflow_dispatch` for workflow files on the default branch, which this one is not). Run 42669608180 was green in 5 minutes 29 seconds (`run_duration_ms: 329000`) and uploaded `Cookbook-1.0.0-arm64.dmg` (146 MB). App name and bundle id come from `.env.example`: `APP_NAME=Cookbook`, `NATIVEPHP_APP_ID=be.matsevh.cookbook` (user decision).

Without Apple credentials the build does not fail. The log shows `falling back to ad-hoc signature for macOS application code signing`, `identityName=-`, and `skipping notarizing, NATIVEPHP_APPLE_ID, NATIVEPHP_APPLE_ID_PASS and NATIVEPHP_APPLE_TEAM_ID env variables must be set.` It also warns `* * * INSECURE BUILD * * * Secure app bundle not found! Building with exposed source files.` (the build log links https://nativephp.com/docs/publishing/building#security; the guide itself is at https://nativephp.com/docs/desktop/2/digging-deeper/security), which Milestone 8 has to look at.

### Install

- Downloaded through a browser (Zen), the app carries `com.apple.quarantine` and macOS refuses it: "Apple could not verify “Cookbook” is free of malware". `spctl -a -vv -t exec` says `rejected`. Expected for an unnotarized app.
- After `xattr -dr com.apple.quarantine` (run by the user), the app crashes at launch. The crash report (`Cookbook-2026-11-09-191917.ips`) says `Library not loaded: @rpath/Electron Framework.framework/Electron Framework ... not valid for use in process: mapping process and mapped file (non-platform) have different Team IDs`. The ad-hoc signature carries the hardened runtime (`flags=0x10002(adhoc,runtime)`), so library validation is on, and ad-hoc signatures have no Team ID. The entitlements in the build are `allow-jit`, `allow-unsigned-executable-memory` and `allow-dyld-environment-variables`, without `com.apple.security.cs.disable-library-validation`.
- The same app re-signed ad hoc without the hardened runtime (`codesign --force --deep --sign -`, giving `flags=0x2(adhoc)`) starts normally. This is the plan's revisit case for Step 1.5: the build as produced does not open; an ad-hoc re-sign in the workflow does. Decision at the Step 1.7 gate.
- The `gh`-downloaded DMG had only `com.apple.provenance`, no quarantine flag. `curl` is not expected to set it either, but that is not tested, so `install.sh` may not need the `xattr` step. The NativePHP building page warns that without notarization the app "will only run on the development machine" (https://nativephp.com/docs/desktop/2/publishing/building); that matches the Gatekeeper refusal above and is what removing the quarantine flag works around.

### The re-signed app, launched with `open`

- NativePHP's server on 8100 and the MCP listener on `127.0.0.1:47123` start; storage is `~/Library/Application Support/cookbook/storage`, the token file there is created with mode `-rw-------`.
- `ping` returns `pong`. After `POST <api>/window/close` the process stays alive and `ping` still returns `pong` ten seconds later.
- `spike_git_branch` returns exit code 0 and `feature/m1-spikes` for `~/Personal/Cookbook`, a folder outside the bundle. The `PATH` it sees includes the user's shell paths, because NativePHP calls `fix-path` at startup (`resources/electron/src/main/index.js`).
- `GET /` on NativePHP's own port returns 200 without the secret, so the 403 guard is also off in the packaged build.
- `App::openAtLogin()` reports `{"open":true}` with `APP_ENV=production`.
- Menu bar: with the window closed the menu bar item was there and "Open Cookbook" brought the window back (checked by the user).
- Launch at login: after a logout and login (`last`: new console session at 20:34), Cookbook was running from 20:35:05 with `launchd` (pid 1) as its parent. `ping` returned `pong`, and `spike_git_branch` returned exit code 0 and `feature/m1-spikes`, so git is found from a login-started app; NativePHP's `fix-path` gives it the login shell `PATH`. The login item points at the re-signed test copy; it is removed at Step 1.7.

### Decision

The free route works, with one change: the DMG as built does not open, because ad-hoc signing with the hardened runtime breaks library validation. Re-signed ad hoc without the hardened runtime, the installed app opens, stays alive with its window closed, serves MCP and starts at login. How the release workflow produces that build (an ad-hoc re-sign step after `native:build`, or adding `com.apple.security.cs.disable-library-validation`) is a revisit decision for the Step 1.7 gate.

## Minutes and updater

Spec risks 5 and 6. Checked on 2026-11-09.

### Actions minutes

GitHub's runner reference (https://docs.github.com/en/actions/reference/runners/github-hosted-runners) says: "Use of the standard GitHub-hosted runners is free and unlimited on public repositories." Larger runners are billed, also for public repositories (https://docs.github.com/en/billing/concepts/product-billing/github-actions). The Step 1.5 run confirms it: the job ran on the label `macos-latest`, and `gh api /repos/Matse2005/cookbook/actions/runs/42669608180/timing` returned `{"billable":{"MACOS":{"total_ms":0,...}},"run_duration_ms":329000}`, so 5.5 minutes of macOS time billed as zero. Every workflow keeps to standard labels (`macos-latest`, `ubuntu-latest`). The run also carried the annotation that macOS arm64 jobs may queue longer because of capacity.

### Updater

From https://nativephp.com/docs/desktop/2/publishing/updating: "macOS: Automatic updating is only supported for signed applications" and "The updater will only run when your app is running in production mode." The page does not mention notarization, so whether notarization is required on top of signing is unconfirmed.

The published `config/nativephp.php` (https://nativephp.com/docs/desktop/2/getting-started/configuration) configures the updater with `NATIVEPHP_UPDATER_ENABLED` (default `true`) and `NATIVEPHP_UPDATER_PROVIDER` (default `spaces`). The `github` provider reads `GITHUB_REPO`, `GITHUB_OWNER`, `GITHUB_TOKEN`, `GITHUB_V_PREFIXED_TAG_NAME` (default `true`), `GITHUB_PRIVATE` (default `false`), `GITHUB_AUTOUPDATE_TOKEN` (read-only token for private repositories), `GITHUB_CHANNEL` (default `latest`) and `GITHUB_RELEASE_TYPE` (default `draft`). `GITHUB_*` is in `cleanup_env_keys`, so those values are stripped from the bundled `.env`.

Spec risk 6 (the updater switches on cleanly once signing exists) cannot be tested without an Apple Developer ID and stays unverified. One thing for Milestone 8: the updater defaults to enabled, so the unsigned release build should set `NATIVEPHP_UPDATER_ENABLED=false` until signing exists, and the in-app banner from Step 8.3 does the update notice instead. Whether the enabled updater causes errors in the current unsigned build was not checked.
