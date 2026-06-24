<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ManagesResearchOutputAssets
{
    protected function researchOutputAssetFromSession(Request $request, string $token, string $extension, string $assetSessionPrefix)
    {
        $assets = collect($request->session()->all())
            ->filter(fn ($value, $key) => str_starts_with((string) $key, $assetSessionPrefix))
            ->flatMap(fn ($value) => is_array($value) ? $value : [])
            ->all();

        $asset = $assets[$token] ?? null;

        if (! is_array($asset) || ($asset['extension'] ?? null) !== $extension) {
            abort(404);
        }

        $path = $asset['path'] ?? '';

        if (! is_string($path) || ! is_file($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => $asset['mime'] ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . ($asset['download'] ?? basename($path)) . '"',
        ]);
    }

    protected function inlineResearchOutputImage(?string $path): ?string
    {
        if (! is_string($path) || ! is_file($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
    }

    protected function researchOutputAssetRecord(string $path, string $extension, string $download): array
    {
        return [
            'path' => $path,
            'extension' => $extension,
            'mime' => match ($extension) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                'pdf' => 'application/pdf',
                default => 'application/octet-stream',
            },
            'download' => $download,
        ];
    }

    protected function forgetResearchOutputSessionAssets(
        Request $request,
        string $htmlSessionPrefix,
        string $assetSessionPrefix,
        array $allowedTemporaryPrefixes,
        array $exactSessionKeys = []
    ): void {
        $session = $request->session();

        collect($session->all())
            ->filter(fn ($value, $key) => str_starts_with((string) $key, $assetSessionPrefix))
            ->each(function ($assets) use ($allowedTemporaryPrefixes) {
                if (is_array($assets)) {
                    $this->deleteResearchOutputAssets($assets, $allowedTemporaryPrefixes);
                }
            });

        $keys = collect(array_keys($session->all()))
            ->filter(fn ($key) => in_array((string) $key, $exactSessionKeys, true)
                || str_starts_with((string) $key, $htmlSessionPrefix)
                || str_starts_with((string) $key, $assetSessionPrefix));

        foreach ($keys as $key) {
            $session->forget($key);
        }
    }

    protected function deleteResearchOutputAssets(array $assets, array $allowedTemporaryPrefixes): void
    {
        $directories = [];

        foreach ($assets as $asset) {
            $path = is_array($asset) ? ($asset['path'] ?? null) : null;

            if (! is_string($path) || ! $this->isAllowedResearchOutputAssetPath($path, $allowedTemporaryPrefixes)) {
                continue;
            }

            if (is_file($path)) {
                @unlink($path);
            }

            $directories[] = dirname($path);
        }

        foreach (array_unique($directories) as $directory) {
            $this->removeResearchOutputTemporaryDirectory($directory);
        }
    }

    protected function removeResearchOutputTemporaryDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (glob($directory . '/*') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        @rmdir($directory);
    }

    protected function isAllowedResearchOutputAssetPath(string $path, array $allowedTemporaryPrefixes): bool
    {
        foreach ($allowedTemporaryPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
