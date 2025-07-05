<script setup>
import { ref } from 'vue'
import { Bars3Icon, XMarkIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import AppLogo from '@/components/ui/AppLogo.vue'
import SearchBar from '@/components/search/SearchBar.vue'

const props = defineProps({
  isSidebarOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggle-sidebar'])
const showMobileSearch = ref(false)

const toggleSidebar = () => {
  emit('toggle-sidebar')
}

const toggleMobileSearch = () => {
  showMobileSearch.value = !showMobileSearch.value
}
</script>

<template>
  <header class="bg-white dark:bg-gray-800 shadow-sm z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Left section -->
        <div class="flex items-center">
          <!-- Mobile menu button -->
          <button
            type="button"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500"
            @click="toggleSidebar"
          >
            <span class="sr-only">Open main menu</span>
            <Bars3Icon v-if="!isSidebarOpen" class="block h-6 w-6" aria-hidden="true" />
            <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true" />
          </button>

          <!-- Logo -->
          <div class="flex-shrink-0 flex items-center ml-4">
            <router-link to="/">
              <AppLogo class="h-8 w-auto" />
            </router-link>
          </div>

          <!-- Desktop search -->
          <div class="hidden md:ml-6 md:flex md:items-center">
            <SearchBar />
          </div>
        </div>

        <!-- Right section - Empty for now, can be used for additional controls -->
        <div class="flex items-center">
          <!-- Mobile search button -->
          <div class="md:hidden flex items-center">
            <button
              type="button"
              class="p-2 rounded-full text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              @click="toggleMobileSearch"
            >
              <span class="sr-only">Search</span>
              <MagnifyingGlassIcon class="h-6 w-6" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile search -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showMobileSearch" class="md:hidden px-4 pb-4">
        <SearchBar />
      </div>
    </transition>
  </header>
</template>
