import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/demo-entry.js'],
            refresh: false,
        }),
    ],
    build: {
        outDir: 'storage/app/demo-build',
        emptyOutDir: true,
        rollupOptions: {
            output: {
                inlineDynamicImports: true,
            },
        },
    },
});
