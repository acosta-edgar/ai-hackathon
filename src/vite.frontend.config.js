import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import { fileURLToPath, URL } from 'node:url'
import { resolve } from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js/frontend', import.meta.url)),
      '~': resolve(__dirname, './resources/js/frontend')
    }
  },
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://localhost:8000', // Laravel backend
        changeOrigin: true,
        secure: false,
        ws: true,
        rewrite: (path) => path.replace(/^\/api/, '')
      }
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `@import "@/assets/scss/variables.scss";`
      }
    }
  },
  build: {
    outDir: 'public/frontend',
    emptyOutDir: true,
    assetsDir: 'static',
    rollupOptions: {
      input: {
        app: resolve(__dirname, 'resources/js/frontend/main.js')
      },
      output: {
        manualChunks: {
          'vue': ['vue', 'vue-router', 'pinia'],
          'vendor': ['axios', 'date-fns', 'lodash'],
          'ui': ['@headlessui/vue', '@heroicons/vue'],
        }
      }
    }
  },
  root: resolve(__dirname, 'resources/js/frontend')
}) 