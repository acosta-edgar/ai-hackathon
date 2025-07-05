<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
    <div class="text-center">
      <h1 class="text-6xl font-bold text-gray-900 dark:text-white mb-4">404</h1>
      <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">Oops! Page not found</p>
      <router-link 
        to="/"
        class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors"
      >
        Go back home
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { useHead } from '@vueuse/head'

useHead({
  title: '404 - Page Not Found',
  meta: [
    { name: 'description', content: 'The page you are looking for does not exist.' }
  ]
})
</script>
