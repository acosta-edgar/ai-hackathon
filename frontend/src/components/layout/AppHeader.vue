<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { Bars3Icon, XMarkIcon, MagnifyingGlassIcon, BellIcon } from '@heroicons/vue/24/outline'
import AppLogo from '@/components/ui/AppLogo.vue'
import UserMenu from '@/components/user/UserMenu.vue'
import SearchBar from '@/components/search/SearchBar.vue'

const props = defineProps({
  isSidebarOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggle-sidebar'])

const route = useRoute()
const userStore = useUserStore()
const showMobileSearch = ref(false)
const showNotifications = ref(false)

const toggleSidebar = () => {
  emit('toggle-sidebar')
}

const toggleMobileSearch = () => {
  showMobileSearch.value = !showMobileSearch.value
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
}

// Hide notifications when clicking outside
const handleClickOutside = (event) => {
  const notificationsEl = document.getElementById('notifications-panel')
  const triggerEl = document.getElementById('notifications-trigger')
  
  if (notificationsEl && !notificationsEl.contains(event.target) && 
      triggerEl && !triggerEl.contains(event.target)) {
    showNotifications.value = false
  }
}

// Add click outside listener
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

// Cleanup
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
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

        <!-- Right section -->
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

          <!-- Notifications -->
          <div class="relative ml-4 md:ml-6">
            <button
              id="notifications-trigger"
              type="button"
              class="p-1 rounded-full text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              @click="toggleNotifications"
            >
              <span class="sr-only">View notifications</span>
              <BellIcon class="h-6 w-6" aria-hidden="true" />
              <span 
                v-if="userStore.unreadNotifications > 0"
                class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-800"
              ></span>
            </button>

            <!-- Notifications dropdown -->
            <transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div 
                v-if="showNotifications"
                id="notifications-panel"
                class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg py-1 bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                role="menu"
                aria-orientation="vertical"
                aria-labelledby="user-menu-button"
                tabindex="-1"
              >
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    Notifications
                  </p>
                </div>
                <div class="py-1">
                  <div v-if="userStore.notifications.length === 0" class="px-4 py-4 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No new notifications</p>
                  </div>
                  <template v-else>
                    <a
                      v-for="notification in userStore.notifications.slice(0, 5)"
                      :key="notification.id"
                      href="#"
                      class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                      role="menuitem"
                      tabindex="-1"
                    >
                      <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                          <div 
                            :class="[
                              'h-2 w-2 rounded-full',
                              notification.read ? 'bg-transparent' : 'bg-primary-500'
                            ]"
                          ></div>
                        </div>
                        <div class="ml-3 flex-1">
                          <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ notification.title }}
                          </p>
                          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ notification.message }}
                          </p>
                          <p class="mt-1 text-xs text-gray-400">
                            {{ $filters.relativeTime(notification.created_at) }}
                          </p>
                        </div>
                      </div>
                    </a>
                    <div class="border-t border-gray-200 dark:border-gray-700">
                      <a
                        href="#"
                        class="block px-4 py-2 text-sm font-medium text-center text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
                        role="menuitem"
                        tabindex="-1"
                      >
                        View all notifications
                      </a>
                    </div>
                  </template>
                </div>
              </div>
            </transition>
          </div>

          <!-- User menu -->
          <div class="ml-4 md:ml-6">
            <UserMenu />
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
