import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/ajax.js',
                'resources/js/forms.js',
                'resources/js/datetime.js',
                'resources/js/calendar.js',
                'resources/js/search.js',
                'resources/js/highlight.js',
                'resources/js/auth-validation.js',
                'resources/js/settings.js',
                'resources/js/datatable.js',
                'resources/js/detail-view.js',
                'resources/js/form-wizard.js',
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
