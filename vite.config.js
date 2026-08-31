import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const phpTarget = env.VITE_PHP_API_TARGET || 'http://localhost:8000';
  const remoteApi = env.VITE_REMOTE_API || 'https://new.555xch.pro';

  return {
    plugins: [
      react()
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
