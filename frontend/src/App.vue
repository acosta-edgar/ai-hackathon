<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'
import AppLayout from '@/layouts/AppLayout.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const route = useRoute()
const toast = useToast()

// App state
const isAppReady = ref(true)
const error = ref(null)
const isOnline = ref(navigator.onLine)

// Handle network status changes
const updateNetworkStatus = () => {
  isOnline.value = navigator.onLine
  if (!isOnline.value) {
    toast.warning('You are currently offline. Some features may not be available.')
  }
}

// Initialize app
const initializeApp = () => {
  try {
    // Any app initialization code can go here
    
    // If user is authenticated but on auth page, redirect to dashboard
    if (userStore.isAuthenticated && isAuthPage.value) {
      router.replace({ name: 'dashboard' })
    }
    
    // If user is not authenticated and route requires auth, redirect to login
    if (!userStore.isAuthenticated && route.meta.requiresAuth) {
      router.replace({ 
        name: 'login', 
        query: { redirect: route.fullPath }
      })
    }
    
  } catch (err) {
    console.error('Failed to initialize app:', err)
    error.value = 'Failed to initialize application. Please refresh the page.'
    toast.error(error.value)
  } finally {
    isAppReady.value = true
  }
}

// Lifecycle hooks
onMounted(() => {
  // Set up event listeners
  window.addEventListener('online', updateNetworkStatus)
  window.addEventListener('offline', updateNetworkStatus)
  
  // Initialize the app
  initializeApp()
})

// Clean up event listeners
onBeforeUnmount(() => {
  window.removeEventListener('online', updateNetworkStatus)
  window.removeEventListener('offline', updateNetworkStatus)
})

// Watch for route changes
watch(() => route, handleRouteChange, { immediate: true })
</script>

<template>
  <div class="app-container" :class="{ 'offline': !isOnline }">
    <!-- Loading overlay -->
    <div 
      v-if="!isAppReady" 
      class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white dark:bg-gray-900"
    >
      <LoadingSpinner size="lg" class="mb-4" />
      <p class="text-gray-600 dark:text-gray-400">Loading application...</p>
    </div>
    
    <!-- Error state -->
    <div 
      v-else-if="error"
      class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white dark:bg-gray-900 p-6 text-center"
    >
      <div class="max-w-md">
        <div class="text-red-500 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Something went wrong</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">{{ error }}</p>
        <button
          @click="initializeApp"
          class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          Try Again
        </button>
      </div>
    </div>

    <!-- Online/Offline indicator -->
    <div 
      v-if="!isOnline"
      class="fixed bottom-4 right-4 z-40 px-4 py-2 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 text-sm rounded-md shadow-lg flex items-center"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      You are currently offline
    </div>

    <!-- Main app content -->
    <RouterView v-slot="{ Component, route: currentRoute }">
      <Transition
        :name="currentRoute.meta.transition || 'fade'"
        mode="out-in"
        :appear="true"
      >
        <component 
          :is="isAuthPage ? AuthLayout : AppLayout"
          :key="currentRoute.fullPath"
        >
          <component :is="Component" />
        </component>
      </Transition>
    </RouterView>
  </div>
</template>

<style scoped>
.app-container {
  min-height: 100vh;
  background-color: #f9fafb;
  color: #111827;
  transition: background-color 0.2s, color 0.2s;
}

.dark .app-container {
  background-color: #111827;
  color: #f9fafb;
}

/* Page transition effects */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-left-enter-active,
.slide-left-leave-active,
.slide-right-enter-active,
.slide-right-leave-active {
  transition: transform 0.3s ease-in-out;
}

.slide-left-enter-from {
  transform: translateX(-100%);
}

.slide-left-leave-to {
  transform: translateX(100%);
}

.slide-right-enter-from {
  transform: translateX(100%);
}

.slide-right-leave-to {
  transform: translateX(-100%);
}

/* Offline state */
.offline {
  opacity: 0.9;
}

.offline::after {
  content: '';
  position: fixed;
  inset: 0;
  background-color: rgba(107, 114, 128, 0.5);
  backdrop-filter: blur(1px);
  pointer-events: none;
  z-index: 30;
}

/* Custom scrollbar */
.scrollbar-thin {
  scrollbar-width: thin;
  scrollbar-color: #9ca3af #f3f4f6;
}

.scrollbar-thin::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.scrollbar-thin::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #9ca3af;
  border-radius: 4px;
}

/* Dark mode scrollbar */
.dark .scrollbar-thin {
  scrollbar-color: #4b5563 #1f2937;
}

.dark .scrollbar-thin::-webkit-scrollbar-track {
  background: #1f2937;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #4b5563;
}
</style>
