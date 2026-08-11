import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/forms.js',
                'resources/js/datetime.js',
                'resources/js/tour.js',
                'resources/js/highlight.js',
                'resources/js/newsbox.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            // Package has no "main"; point Vite at the built plugin file.
            'jquery-steps': 'jquery-steps/build/jquery.steps.js',
        },
    },
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
});
