import { defineConfig } from 'vite';

// The entry list is the theme's config/entries.php; the empty starter builds
// nothing yet. A preset may replace this file when it needs a plugin.
export default defineConfig({
	build: {
		outDir: 'build',
		manifest: true,
		emptyOutDir: true,
		rollupOptions: {
			input: {},
		},
	},
});
