<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import { useToast } from 'vue-toastification'

const route = useRoute()
const toast = useToast()

// Layout state
const isSidebarOpen = ref(false)
const isLoading = ref(true)
const isMobile = ref(false)
const error = ref(null)

// Toggle sidebar
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

// Close sidebar on mobile when route changes
watch(() => route.path, () => {
  if (isMobile.value) {
    isSidebarOpen.value = false
  }
})

// Check if mobile view
const checkIfMobile = () => {
  isMobile.value = window.innerWidth < 1024
  // Keep sidebar closed by default on all screen sizes
  isSidebarOpen.value = false
}

// Handle window resize
const handleResize = () => {
  checkIfMobile()
}

// Set page title based on route meta
const pageTitle = computed(() => {
  return route.meta.title || 'JobCompass AI'
})

// Set document title
watch(pageTitle, (newTitle) => {
  document.title = `${newTitle} | JobCompass AI`
}, { immediate: true })

// Fetch initial data
const fetchInitialData = async () => {
  try {
    isLoading.value = true
    
    // Fetch any initial data here (no auth required)
    
  } catch (err) {
    console.error('Failed to load initial data:', err)
    error.value = 'Failed to load application data. Please try again later.'
    toast.error('Failed to load application data')
  } finally {
    isLoading.value = false
  }
}

// Initialize
onMounted(async () => {
  checkIfMobile()
  window.addEventListener('resize', handleResize)
  
  // Fetch initial data
  await fetchInitialData()
})

// Cleanup
onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col">
    <!-- Skip to main content link for accessibility -->
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:p-2 focus:bg-white focus:ring-2 focus:ring-primary-500 focus:rounded-md"
    >
      Skip to main content
    </a>

    <!-- Header -->
    <AppHeader 
      :is-sidebar-open="isSidebarOpen"
      @toggle-sidebar="toggleSidebar"
    />

    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar -->
      <AppSidebar 
        :is-open="isSidebarOpen"
        @close="isSidebarOpen = false"
      />

      <!-- Main content -->
      <main 
        id="main-content"
        class="flex-1 relative z-0 overflow-y-auto focus:outline-none"
        tabindex="0"
      >
        <!-- Loading overlay -->
        <div 
          v-if="isLoading"
          class="fixed inset-0 bg-white dark:bg-gray-900 bg-opacity-75 flex items-center justify-center z-50"
        >
          <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500 mx-auto"></div>
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Loading application...</p>
          </div>
        </div>

        <!-- Error state -->
        <div 
          v-else-if="error"
          class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
        >
          <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-500 p-4 rounded">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-700 dark:text-red-300">
                  {{ error }}
                </p>
              </div>
              <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                  <button 
                    type="button" 
                    class="inline-flex rounded-md bg-red-50 dark:bg-red-900/20 p-1.5 text-red-500 hover:bg-red-100 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50 dark:focus:ring-offset-red-900/20"
                    @click="fetchInitialData"
                  >
                    <span class="sr-only">Retry</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Page content -->
        <div v-else class="flex-1 overflow-auto">
          <!-- Page header -->
          <div class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
              <div class="md:flex md:items-center md:justify-between">
                <div class="min-w-0 flex-1">
                  <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:tracking-tight">
                    {{ pageTitle }}
                  </h1>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                  <slot name="actions"></slot>
                </div>
              </div>
              
              <!-- Breadcrumbs -->
              <nav class="mt  -4 flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-2">
                  <li v-for="(item, index) in route.meta.breadcrumbs || []" :key="index">
                    <div class="flex items-center">
                      <template v-if="index > 0">
                        <svg class="h-5 w-5 flex-shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                          <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                      </template>
                      <router-link 
                        v-if="item.to" 
                        :to="item.to"
                        class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        :class="{ 'ml-2': index > 0 }"
                      >
                        {{ item.label }}
                      </router-link>
                      <span 
                        v-else
                        class="text-sm font-medium text-gray-700 dark:text-gray-300"
                        :class="{ 'ml-2': index > 0 }"
                        aria-current="page"
                      >
                        {{ item.label }}
                      </span>
                    </div>
                  </li>
                </ol>
              </nav>
            </div>
          </div>

          <!-- Main content area -->
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <slot></slot>
          </div>
        </div>

        <!-- Footer -->
        <AppFooter />
      </main>
    </div>
  </div>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>
