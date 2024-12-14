import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
    server: {
        hmr: {
            host: process.env.HMR_HOST || "0.0.0.0",
        },
    },
    resolve: {
        alias: {
            "@mingle": path.resolve(__dirname, "/vendor/ijpatricio/mingle/resources/js"),
        },
    },
    plugins: [
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/QuestionCategorySelector.js', 'resources/js/ExamPrintLayout.js', 'resources/js/LectureVideoList.js', 'resources/js/PageSelector.js'],
            refresh: true,
        }),
    ],
});
