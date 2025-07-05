<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full text-center">
      <div class="text-6xl font-bold text-gray-300 mb-4">404</div>
      <h1 class="text-2xl font-semibold text-gray-900 mb-2">Page Not Found</h1>
      <p class="text-gray-600 mb-6">The page you're looking for doesn't exist.</p>
      <router-link
        to="/"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      >
        Go Home
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFound'
}
</script> 