<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'

const route = useRoute()

// Layout state
const isSidebarOpen = ref(true)
const isMobile = ref(false)

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
  if (!isMobile.value) {
    isSidebarOpen.value = true
  } else {
    isSidebarOpen.value = false
  }
}

// Handle window resize
const handleResize = () => {
  checkIfMobile()
}

// Initialize
onMounted(() => {
  checkIfMobile()
  window.addEventListener('resize', handleResize)
  document.title = 'JobCompass AI'
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

    <!-- Main content -->
    <div class="flex flex-1 overflow-hidden">
      <!-- Sidebar -->
      <AppSidebar 
        :is-open="isSidebarOpen"
        @close="isSidebarOpen = false"
        class="z-40"
      />

      <!-- Main content area -->
      <main 
        id="main-content"
        class="flex-1 overflow-y-auto focus:outline-none"
        tabindex="0"
      >
        <!-- Page content -->
        <div class="flex-1 overflow-auto">
          <!-- Page header -->
          <div class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
              <div class="md:flex md:items-center md:justify-between">
                <div class="min-w-0 flex-1">
                  <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:tracking-tight">
                    {{ route.meta.title || 'JobCompass AI' }}
                  </h1>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                  <slot name="actions"></slot>
                </div>
              </div>
              
              <!-- Breadcrumbs -->
              <nav v-if="route.meta.breadcrumbs" class="mt-4 flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-2">
                  <li v-for="(item, index) in route.meta.breadcrumbs" :key="index">
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
