<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
          Welcome to JobCompass AI
        </h1>
        <p class="mt-5 max-w-xl mx-auto text-xl text-gray-500">
          Your personal job search assistant powered by AI
        </p>
        <div class="mt-8 flex justify-center">
          <router-link
            to="/jobs"
            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Browse Jobs
          </router-link>
          <router-link
            to="/login"
            class="ml-4 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Sign In
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Component logic here
</script>
