<?php

namespace App\Services\Knowledge;

use Illuminate\Support\Facades\Process;

class KnowledgePythonProcess
{
    private ?string $pythonBin = null;

    public function run(array $command, int $timeout)
    {
        $tempPath = $this->prepareTempPath();
        $command[0] = $this->pythonBin();

        return Process::path(base_path())
            ->env($this->environment($tempPath))
            ->timeout($timeout)
            ->run($command);
    }

    public function pythonBin(): string
    {
        if ($this->pythonBin !== null) {
            return $this->pythonBin;
        }

        $configured = config('knowledge.python_bin');
        $candidates = [];

        if (is_string($configured) && trim($configured) !== '') {
            $candidates[] = trim($configured);
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $candidates[] = 'C:/Python314/python.exe';
            $candidates[] = 'C:/Python313/python.exe';
            $candidates[] = 'C:/Python312/python.exe';
            $candidates[] = 'py';
        }

        $candidates[] = 'python3';
        $candidates[] = 'python';

        foreach (array_values(array_unique($candidates)) as $candidate) {
            if ($this->canImportChromadb($candidate)) {
                return $this->pythonBin = $candidate;
            }
        }

        return $this->pythonBin = is_string($configured) && trim($configured) !== '' ? trim($configured) : 'python';
    }

    public function environment(?string $tempPath = null): array
    {
        $tempPath ??= $this->prepareTempPath();

        return [
            'KNOWLEDGE_CHROMA_PATH' => config('knowledge.chroma_path'),
            'KNOWLEDGE_SEARCH_LIMIT' => (string) config('knowledge.search_limit'),
            'KNOWLEDGE_PYTHON_FALLBACK' => 'C:/Python314/python.exe',
            'PYTHONPATH' => $this->pythonPath(),
            'PYTHONUSERBASE' => 'C:/Users/avisala.cebas/AppData/Roaming/Python',
            'PYTHONNOUSERSITE' => '',
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

    private function pythonPath(): string
    {
        $paths = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $paths[] = 'C:/Users/avisala.cebas/AppData/Roaming/Python/Python314/site-packages';
            $paths[] = 'C:/Python314/Lib/site-packages';
        }

        $existing = getenv('PYTHONPATH');

        if (is_string($existing) && $existing !== '') {
            $paths[] = $existing;
        }

        return implode(PATH_SEPARATOR, array_filter(array_unique($paths)));
    }

    private function canImportChromadb(string $pythonBin): bool
    {
        if ($this->isAbsoluteWindowsPath($pythonBin) && ! file_exists($pythonBin)) {
            return false;
        }

        try {
            $result = Process::path(base_path())
                ->env($this->environment())
                ->timeout(15)
                ->run([$pythonBin, '-c', 'import chromadb']);

            return $result->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    private function isAbsoluteWindowsPath(string $path): bool
    {
        return strlen($path) >= 3
            && ctype_alpha($path[0])
            && $path[1] === ':'
            && in_array($path[2], ['\\', '/'], true);
    }

    private function absolutePath(string $path): string
    {
        if ($path === '') {
            return storage_path('app/private/knowledge/tmp');
        }

        if ($this->isAbsoluteWindowsPath($path) || str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }
}
