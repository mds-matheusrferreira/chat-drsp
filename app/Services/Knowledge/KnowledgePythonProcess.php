<?php

namespace App\Services\Knowledge;

use Illuminate\Support\Facades\Process;

class KnowledgePythonProcess
{
    public function run(array $command, int $timeout)
    {
        $tempPath = $this->prepareTempPath();

        return Process::path(base_path())
            ->env($this->environment($tempPath))
            ->timeout($timeout)
            ->run($command);
    }

    public function environment(?string $tempPath = null): array
    {
        $tempPath ??= $this->prepareTempPath();

        return [
            'KNOWLEDGE_CHROMA_PATH' => config('knowledge.chroma_path'),
            'KNOWLEDGE_SEARCH_LIMIT' => (string) config('knowledge.search_limit'),
            'TMP' => $tempPath,
            'TEMP' => $tempPath,
            'TMPDIR' => $tempPath,
        ];
    }

    public function prepareTempPath(): string
    {
        $path = $this->absolutePath(config('knowledge.tmp_path'));

        if (! is_dir($path)) {
            mkdir($path, 0775, true);
        }

        putenv('TMP='.$path);
        putenv('TEMP='.$path);
        putenv('TMPDIR='.$path);

        $_ENV['TMP'] = $path;
        $_ENV['TEMP'] = $path;
        $_ENV['TMPDIR'] = $path;
        $_SERVER['TMP'] = $path;
        $_SERVER['TEMP'] = $path;
        $_SERVER['TMPDIR'] = $path;

        return $path;
    }

    private function absolutePath(string $path): string
    {
        if ($path === '') {
            return storage_path('app/private/knowledge/tmp');
        }

        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }
}
