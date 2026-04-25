import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig(({ command, mode }) => {
	if (command === 'build' && mode === 'legacy') {
		return {
			build: {
				outDir: 'public/build-legacy',
				manifest: 'manifest.json',
				rollupOptions: {
					input: 'resources/js/app-legacy.js',
					output: {
						format: 'iife',
						name: 'Main',
						strict: false,
					}
				},
				minify: false,
			},
			publicDir: false,
		};
	}
	return {
		plugins: [
			laravel({
				input: [
					'resources/scss/app.scss', 
					'resources/scss/accom-report.scss',
					'resources/scss/gpoa-report.scss',
					'resources/js/app.js',
				],
				refresh: true,
			}),
		],
		build: {
			minify: false,
			cssMinify: false
		},
		server: {
            watch: {
                ignored: ['**/vendor/**', '**/storage/**'],
            },
			proxy: {
				'/font': {
					target: 'http://localhost:5173',
					changeOrigin: true,
					rewrite: (path) => `/public${path}`,
				},
				'/images': {
					target: 'http://localhost:5173',
					changeOrigin: true,
					rewrite: (path) => `/public${path}`,
				},
				'/storage': {
					target: 'http://localhost:5173',
					changeOrigin: true,
					rewrite: (path) => `/public${path}`,
				}
			}
		}
	};
});
