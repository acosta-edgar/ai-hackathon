<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import {
  HomeIcon,
  BriefcaseIcon,
  DocumentTextIcon,
  UserIcon,
  Cog6ToothIcon,
  ArrowLeftOnRectangleIcon,
  ArrowRightOnRectangleIcon,
  ChevronRightIcon,
  ChevronDownIcon,
  PlusIcon,
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
const activeSubmenu = ref(null)

// Navigation items
const navItems = computed(() => [
  { 
    name: 'Dashboard', 
    href: { name: 'dashboard' }, 
    icon: HomeIcon,
    current: route.name === 'dashboard'
  },
  { 
    name: 'Jobs', 
    href: { name: 'jobs' }, 
    icon: BriefcaseIcon,
    current: route.name?.startsWith('jobs')
  },
  { 
    name: 'Applications', 
    href: { name: 'applications' }, 
    icon: DocumentTextIcon,
    current: route.name?.startsWith('applications')
  },
  { 
    name: 'Profile', 
    href: { name: 'profile' }, 
    icon: UserIcon,
    current: route.name?.startsWith('profile')
  },
  { 
    name: 'Settings', 
    href: { name: 'settings' }, 
    icon: Cog6ToothIcon,
    current: route.name?.startsWith('settings'),
    children: [
      { name: 'Account', href: { name: 'settings.account' } },
      { name: 'Notifications', href: { name: 'settings.notifications' } },
      { name: 'Billing', href: { name: 'settings.billing' } }
    ]
  }
])

// Toggle submenu
const toggleSubmenu = (index) => {
  activeSubmenu.value = activeSubmenu.value === index ? null : index
}
</script>

<template>
  <div>
    <!-- Backdrop for all screen sizes -->
    <transition
      enter-active-class="transition-opacity ease-linear duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-linear duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="isOpen"
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
          class="p-1 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 lg:hidden"
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
            <div>
              <router-link
                v-if="!item.children"
                :to="item.href"
                class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                :class="[
                  item.current
                    ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white',
                ]"
              >
                <component
                  :is="item.icon"
                  class="mr-3 flex-shrink-0 h-6 w-6"
                  :class="[
                    item.current
                      ? 'text-primary-500'
                      : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                  ]"
                  aria-hidden="true"
                />
                <span class="flex-1">{{ item.name }}</span>
                <span 
                  v-if="item.count"
                  class="bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-primary-900 dark:text-primary-300"
                >
                  {{ item.count }}
                </span>
              </router-link>

              <div v-else>
                <button
                  type="button"
                  class="group w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
                  @click="toggleSubmenu(index)"
                >
                  <div class="flex items-center">
                    <component
                      :is="item.icon"
                      class="mr-3 flex-shrink-0 h-6 w-6 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300"
                      aria-hidden="true"
                    />
                    <span>{{ item.name }}</span>
                  </div>
                  <component
                    :is="activeSubmenu === index ? ChevronDownIcon : ChevronRightIcon"
                    class="ml-1 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300"
                    aria-hidden="true"
                  />
                </button>

                <transition
                  enter-active-class="transition-all duration-200 ease-in-out overflow-hidden"
                  enter-from-class="max-h-0 opacity-0"
                  enter-to-class="max-h-96 opacity-100"
                  leave-active-class="transition-all duration-200 ease-in-out overflow-hidden"
                  leave-from-class="max-h-96 opacity-100"
                  leave-to-class="max-h-0 opacity-0"
                >
                  <div v-if="activeSubmenu === index" class="pl-11 mt-1 space-y-1">
                    <router-link
                      v-for="subItem in item.children"
                      :key="subItem.name"
                      :to="subItem.href"
                      class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
                      :class="[
                        route.name === subItem.href.name
                          ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white',
                      ]"
                    >
                      <span class="flex-1">{{ subItem.name }}</span>
                    </router-link>
                  </div>
                </transition>
              </div>
            </div>
          </template>
        </nav>

        <!-- Bottom section -->
        <div class="mt-auto px-4 py-4 border-t border-gray-200 dark:border-gray-700">
          <!-- Quick actions -->
          <div class="space-y-1 mb-4">
            <button
              type="button"
              class="group w-full flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white"
            >
              <PlusIcon class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" />
              <span>Add Job</span>
            </button>
          </div>

          <!-- App Info -->
          <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="text-center">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                JobCompass AI
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                AI-Powered Job Matching
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
