<?php

namespace App\Support;

use RuntimeException;

class Vite
{
    /**
     * Render <link>/<script> tags for the given Vite entrypoints.
     *
     * @param  string|array  $entrypoints
     * @return string
     */
    public static function tags($entrypoints): string
    {
        $entrypoints = (array) $entrypoints;
        $hotFile = public_path('hot');

        if (is_file($hotFile)) {
            $host = trim((string) file_get_contents($hotFile));
            $html = '<script type="module" src="'.e(rtrim($host, '/').'/@vite/client').'"></script>';

            foreach ($entrypoints as $entrypoint) {
                $html .= '<script type="module" src="'.e(rtrim($host, '/').'/'.ltrim($entrypoint, '/')).'"></script>';
            }

            return $html;
        }

        $manifestPath = public_path('build/manifest.json');

        if (! is_file($manifestPath)) {
            throw new RuntimeException(
                'Vite manifest not found at public/build/manifest.json. Run `npm run build`.'
            );
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        if (! is_array($manifest)) {
            throw new RuntimeException('Vite manifest is invalid JSON.');
        }

        $html = '';

        foreach ($entrypoints as $entrypoint) {
            if (! isset($manifest[$entrypoint])) {
                throw new RuntimeException("Unable to locate Vite entry [{$entrypoint}] in manifest.");
            }

            $chunk = $manifest[$entrypoint];

            foreach ($chunk['css'] ?? [] as $cssFile) {
                $html .= '<link rel="stylesheet" href="'.e(asset('build/'.$cssFile)).'">';
            }

            if (! empty($chunk['file'])) {
                $html .= '<script type="module" src="'.e(asset('build/'.$chunk['file'])).'"></script>';
            }
        }

        return $html;
    }
}
