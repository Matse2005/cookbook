<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Symfony\Component\Process\Process;

/**
 * Throwaway spike tool for Step 1.5, deleted at Step 1.7: proves the packaged app can
 * read a folder outside its bundle and run git with the environment it gets at login.
 */
#[Name('spike_git_branch')]
#[Description('Spike: report the git branch of ~/Personal/Cookbook as seen by the running app.')]
class SpikeGitBranchTool extends Tool
{
    public function handle(Request $request): Response
    {
        $folder = rtrim((string) getenv('HOME'), '/').'/Personal/Cookbook';

        $process = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], $folder);
        $process->run();

        return Response::text(json_encode([
            'folder' => $folder,
            'folder_readable' => is_readable($folder.'/.git/HEAD'),
            'exit_code' => $process->getExitCode(),
            'branch' => trim($process->getOutput()),
            'error' => trim($process->getErrorOutput()),
            'path' => getenv('PATH'),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
