import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { templateCompilerOptions } from '@tresjs/core'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue({
      ...templateCompilerOptions
    }),
  ],
  build: {
    chunkSizeWarningLimit: 1000,
    rollupOptions: {
      output: {
        manualChunks: {
          'chartjs': ['chart.js'],
          'three':   ['three', '@tresjs/core', '@tresjs/cientos'],
          'vendor':  ['vue', 'vue-router', 'pinia'],
          'lucide':  ['lucide-vue-next'],
        }
      }
    }
  }
})
