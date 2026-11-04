import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Eigener Port, damit keine anderen Projekte (z. B. auf 5173) gestört werden
        port: 5186,
        strictPort: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
