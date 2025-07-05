<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  HomeIcon,
  BriefcaseIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['close'])

const route = useRoute()
// No active submenu needed in the simplified version
const isMobile = ref(false)

// Navigation items
const navItems = computed(() => [
  { 
    name: 'Home', 
    href: { name: 'Home' }, 
    icon: HomeIcon,
    current: route.name === 'Home'
  },
  { 
    name: 'Jobs', 
    href: { name: 'Jobs' }, 
    icon: BriefcaseIcon,
    current: route.name?.startsWith('Job')
  }
])

// Check if mobile view
const checkIfMobile = () => {
  isMobile.value = window.innerWidth < 1024
  if (!isMobile.value && !props.isOpen) {
    emit('close')
  }
}

// Close sidebar when clicking outside on mobile
const handleClickOutside = (event) => {
  const sidebar = document.getElementById('sidebar')
  const toggleButton = document.querySelector('[aria-label="Open main menu"]')
  
  if (sidebar && 
      !sidebar.contains(event.target) && 
      toggleButton && 
      !toggleButton.contains(event.target) &&
      isMobile.value) {
    emit('close')
  }
}

// Add event listeners
onMounted(() => {
  checkIfMobile()
  window.addEventListener('resize', checkIfMobile)
  document.addEventListener('click', handleClickOutside)
})

// Cleanup
onUnmounted(() => {
  window.removeEventListener('resize', checkIfMobile)
  document.removeEventListener('click', handleClickOutside)
})

// Close sidebar on mobile when route changes
watch(() => route.path, () => {
  if (isMobile.value) {
    emit('close')
  }
})
</script>

<template>
  <div>
    <!-- Mobile backdrop -->
    <transition
      enter-active-class="transition-opacity ease-linear duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-linear duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="isOpen && isMobile"
        class="fixed inset-0 bg-gray-600 bg-opacity-75 z-20"
        aria-hidden="true"
        @click="$emit('close')"
      ></div>
    </transition>

    <!-- Sidebar -->
    <div
      id="sidebar"
      class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 bg-white dark:bg-gray-800 shadow-lg transform transition-transform duration-300 ease-in-out"
      :class="{
        'translate-x-0': isOpen,
        '-translate-x-full': !isOpen
      }"
    >
      <!-- Sidebar header -->
      <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
        <router-link to="/" class="flex items-center">
          <img 
            class="h-8 w-auto" 
            src="@/assets/images/logo-icon.svg" 
            alt="JobCompass AI"
          >
          <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">JobCompass</span>
        </router-link>
        <button
          type="button"
          class="p-1 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
          @click="$emit('close')"
        >
          <span class="sr-only">Close sidebar</span>
          <XMarkIcon class="h-6 w-6" aria-hidden="true" />
        </button>
      </div>

      <!-- Sidebar content -->
      <div class="flex-1 flex flex-col overflow-y-auto">
        <!-- Navigation -->
        <nav class="px-2 py-4 space-y-1">
          <template v-for="(item, index) in navItems" :key="item.name">
            <router-link
              v-slot="{ isActive }"
              :to="item.href"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
              :class="[
                isActive || item.current
                  ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white',
              ]"
            >
              <component
                :is="item.icon"
                class="mr-3 flex-shrink-0 h-6 w-6"
                :class="[
                  isActive || item.current
                    ? 'text-primary-500'
                    : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                ]"
                aria-hidden="true"
              />
              {{ item.name }}
            </router-link>
          </template>
        </nav>
      </div>
    </div>
  </div>
</template>
