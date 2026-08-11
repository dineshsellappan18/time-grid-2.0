import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/less/app.less',
                'resources/js/app.js',
                'resources/js/forms.js',
                'resources/js/datetime.js',
                'resources/js/tour.js',
                'resources/js/highlight.js',
                'resources/js/newsbox.js',
                'resources/less/styles.less',
                'resources/less/forms.less',
                'resources/less/datetime.less',
                'resources/less/tour.less',
                'resources/less/highlight.less',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            less: {
                math: 'always',
            },
        },
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
});
