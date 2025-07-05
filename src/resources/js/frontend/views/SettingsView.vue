<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>
      <p class="mt-2 text-gray-600 dark:text-gray-300">Manage your account settings and preferences</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Settings Navigation -->
      <div class="lg:col-span-1">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
          <nav class="space-y-1">
            <router-link
              v-for="item in settingsNav"
              :key="item.name"
              :to="item.href"
              v-slot="{ isActive }"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-md"
              :class="[
                isActive
                  ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300'
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white',
              ]"
            >
              <component
                :is="item.icon"
                class="mr-3 flex-shrink-0 h-5 w-5"
                :class="[
                  isActive
                    ? 'text-primary-500'
                    : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300',
                ]"
                aria-hidden="true"
              />
              {{ item.name }}
            </router-link>
          </nav>
        </div>
      </div>

      <!-- Settings Content -->
      <div class="lg:col-span-3">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  UserIcon,
  BellIcon,
  CreditCardIcon
} from '@heroicons/vue/24/outline'

const settingsNav = [
  {
    name: 'Account',
    href: { name: 'settings.account' },
    icon: UserIcon
  },
  {
    name: 'Notifications',
    href: { name: 'settings.notifications' },
    icon: BellIcon
  },
  {
    name: 'Billing',
    href: { name: 'settings.billing' },
    icon: CreditCardIcon
  }
]
</script> 