<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { useToast } from 'vue-toastification'
import { 
  UserCircleIcon, 
  Cog6ToothIcon, 
  ArrowLeftOnRectangleIcon, 
  UserIcon,
  EnvelopeIcon,
  ShieldCheckIcon,
  QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const userStore = useUserStore()
const toast = useToast()

// Menu state
const isOpen = ref(false)
const userMenuButton = ref(null)
const userMenu = ref(null)

// User data (replace with actual user data from store)
const user = computed(() => userStore.user || {
  name: 'User Name',
  email: 'user@example.com',
  avatar_url: null,
  unread_messages: 3
})

// Menu items
const menuItems = computed(() => [
  { 
    name: 'Your Profile', 
    href: { name: 'profile' }, 
    icon: UserIcon,
    divider: false
  },
  { 
    name: 'Messages', 
    href: { name: 'messages' }, 
    icon: EnvelopeIcon,
    badge: user.value.unread_messages > 0 ? user.value.unread_messages : null,
    divider: false
  },
  { 
    name: 'Settings', 
    href: { name: 'settings' }, 
    icon: Cog6ToothIcon,
    divider: true
  },
  { 
    name: 'Help & Support', 
    href: { name: 'help' }, 
    icon: QuestionMarkCircleIcon,
    divider: false
  },
  { 
    name: 'Privacy', 
    href: { name: 'privacy' }, 
    icon: ShieldCheckIcon,
    divider: true
  },
  { 
    name: 'Sign out', 
    action: 'logout', 
    icon: ArrowLeftOnRectangleIcon,
    danger: true,
    divider: false
  }
])

// Toggle user menu
const toggleMenu = () => {
  isOpen.value = !isOpen.value
}

// Close menu when clicking outside
const handleClickOutside = (event) => {
  if (userMenuButton.value && !userMenuButton.value.contains(event.target) &&
      userMenu.value && !userMenu.value.contains(event.target)) {
    isOpen.value = false
  }
}

// Handle menu item click
const handleMenuItemClick = async (item) => {
  if (item.action === 'logout') {
    await handleLogout()
  } else if (item.href) {
    router.push(item.href)
  }
  isOpen.value = false
}

// Handle logout
const handleLogout = async () => {
  try {
    await userStore.logout()
    toast.success('You have been signed out')
    router.push({ name: 'login' })
  } catch (error) {
    console.error('Logout failed:', error)
    toast.error('Failed to sign out. Please try again.')
  }
}

// Add event listeners
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleEscapeKey)
})

// Remove event listeners
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscapeKey)
})

// Handle escape key
const handleEscapeKey = (event) => {
  if (event.key === 'Escape' && isOpen.value) {
    isOpen.value = false
  }
}
</script>

<template>
  <div class="relative ml-3">
    <div>
      <button
        ref="userMenuButton"
        type="button"
        class="flex max-w-xs items-center rounded-full bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
        id="user-menu-button"
        aria-expanded="false"
        aria-haspopup="true"
        @click="toggleMenu"
      >
        <span class="sr-only">Open user menu</span>
        
        <!-- User avatar -->
        <div v-if="user.avatar_url" class="h-8 w-8 rounded-full overflow-hidden">
          <img 
            class="h-full w-full object-cover" 
            :src="user.avatar_url" 
            :alt="user.name"
          >
        </div>
        
        <!-- Fallback avatar with initials -->
        <div 
          v-else 
          class="h-8 w-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-300 font-medium text-sm"
        >
          {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
        </div>
        
        <!-- Dropdown indicator -->
        <svg 
          class="ml-1 h-4 w-4 text-gray-400" 
          xmlns="http://www.w3.org/2000/svg" 
          viewBox="0 0 20 20" 
          fill="currentColor" 
          aria-hidden="true"
          :class="{ 'rotate-180': isOpen }"
        >
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>

    <!-- Dropdown menu -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-show="isOpen"
        ref="userMenu"
        class="absolute right-0 z-10 mt-2 w-64 origin-top-right rounded-md bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu-button"
        tabindex="-1"
      >
        <!-- User info -->
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
          <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
            {{ user.name || 'User' }}
          </p>
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">
            {{ user.email || 'user@example.com' }}
          </p>
        </div>
        
        <!-- Menu items -->
        <div class="py-1" role="none">
          <template v-for="(item, index) in menuItems" :key="index">
            <a
              v-if="!item.divider"
              href="#"
              class="group flex items-center px-4 py-2 text-sm"
              :class="[
                item.danger 
                  ? 'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20' 
                  : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50',
                'relative'
              ]"
              role="menuitem"
              tabindex="-1"
              @click.prevent="handleMenuItemClick(item)"
            >
              <component
                :is="item.icon"
                class="mr-3 h-5 w-5"
                :class="item.danger ? 'text-red-500' : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'"
                aria-hidden="true"
              />
              <span>{{ item.name }}</span>
              
              <!-- Badge for notifications -->
              <span 
                v-if="item.badge"
                class="ml-auto inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300"
              >
                {{ item.badge }}
              </span>
            </a>
            
            <!-- Divider -->
            <div 
              v-if="item.divider" 
              class="my-1 border-t border-gray-100 dark:border-gray-700" 
              role="separator"
            ></div>
          </template>
        </div>
      </div>
    </transition>
  </div>
</template>
