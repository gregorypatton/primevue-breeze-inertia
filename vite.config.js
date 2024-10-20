import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

// https://vitejs.dev/config/
export default ({ mode }) => {
    const env = loadEnv(mode, process.cwd());
    const devPort = parseInt(env.VITE_PORT) || 5173;
    const hostDomain = env.VITE_HOST_DOMAIN || 'localhost';

    return defineConfig({
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, './resources/js')
            },
        },
        server: {
            port: devPort,
            hmr: {
                host: hostDomain,
            },
            watch: {
                usePolling: true,
            },
        },
        preview: {
            port: devPort,
        },
    });
};
