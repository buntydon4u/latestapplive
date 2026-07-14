import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const phpTarget = env.VITE_PHP_API_TARGET || 'http://localhost:8000';
  const remoteApi = env.VITE_REMOTE_API || 'https://new.555xch.pro';

  return {
    plugins: [
      react(),
      VitePWA({
        registerType: 'autoUpdate',
        manifest: {
          name: 'XCH555',
          short_name: 'XCH555',
          description: 'XCH555 web application',
          theme_color: '#111111',
          background_color: '#111111',
          display: 'standalone',
          display_override: ['standalone'],
          orientation: 'portrait',
          id: '/',
          start_url: '/login',
          scope: '/',
          prefer_related_applications: false,
          icons: [
            { src: '/pwa-192x192.png', sizes: '192x192', type: 'image/png' },
            { src: '/pwa-512x512.png', sizes: '512x512', type: 'image/png' },
            {
              src: '/pwa-512x512.png',
              sizes: '512x512',
              type: 'image/png',
              purpose: 'maskable'
            }
          ]
        },
        workbox: {
          cleanupOutdatedCaches: true,
          navigateFallback: '/index.html',
          navigateFallbackDenylist: [/^\/api(?:\/|$)/, /^\/index\.php(?:\/|$)/],
          globPatterns: ['**/*.{js,css,html,png,svg,ico,woff,woff2}'],
          runtimeCaching: [
            {
              urlPattern: ({ url }) =>
                url.pathname.startsWith('/api') ||
                url.pathname.startsWith('/index.php'),
              handler: 'NetworkOnly'
            }
          ]
        }
      })
    ],
    server: {
      proxy: {
        '/api': {
          target: phpTarget,
          changeOrigin: true,
          secure: false,
          rewrite: (path) => path.replace(/^\/api/, '/api.php')
        },
        '/index.php': {
          target: remoteApi,
          changeOrigin: true,
          secure: true
        }
      }
    }
  };
});
