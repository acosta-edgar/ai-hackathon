import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import { fileURLToPath, URL } from 'node:url'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
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
    outDir: '../public',
    emptyOutDir: true,
    assetsDir: 'static',
    rollupOptions: {
      output: {
        manualChunks: {
          'vue': ['vue', 'vue-router', 'pinia'],
          'vendor': ['axios', 'date-fns', 'lodash'],
          'ui': ['@headlessui/vue', '@heroicons/vue'],
        }
      }
    }
  }
})
