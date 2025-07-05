<template>
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Account Settings</h2>
    
    <div class="space-y-6">
      <!-- Password Change -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Change Password</h3>
        <form @submit.prevent="changePassword" class="space-y-4 max-w-md">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
            <input
              v-model="passwordForm.currentPassword"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
            <input
              v-model="passwordForm.newPassword"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm New Password</label>
            <input
              v-model="passwordForm.confirmPassword"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            >
          </div>
          <button
            type="submit"
            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Change Password
          </button>
        </form>
      </div>

      <!-- Email Preferences -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Email Preferences</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Job Alerts</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Receive notifications about new job opportunities</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="emailPreferences.jobAlerts"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Application Updates</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Get notified about your application status</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="emailPreferences.applicationUpdates"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Newsletter</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Receive our weekly newsletter with career tips</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="emailPreferences.newsletter"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
        </div>
      </div>

      <!-- Privacy Settings -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Privacy Settings</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Profile Visibility</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Allow employers to view your profile</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="privacySettings.profileVisible"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Data Analytics</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Help us improve by sharing anonymous usage data</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="privacySettings.dataAnalytics"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div>
        <h3 class="text-md font-medium text-red-600 dark:text-red-400 mb-4">Danger Zone</h3>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-red-800 dark:text-red-200">Delete Account</p>
              <p class="text-sm text-red-600 dark:text-red-300">Permanently delete your account and all data</p>
            </div>
            <button
              @click="showDeleteConfirm = true"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              Delete Account
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3 text-center">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Account</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Are you sure you want to delete your account? This action cannot be undone.
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <button
              @click="deleteAccount"
              class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
            >
              Yes, Delete Account
            </button>
            <button
              @click="showDeleteConfirm = false"
              class="mt-2 px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const showDeleteConfirm = ref(false)

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const emailPreferences = ref({
  jobAlerts: true,
  applicationUpdates: true,
  newsletter: false
})

const privacySettings = ref({
  profileVisible: true,
  dataAnalytics: true
})

const changePassword = () => {
  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    alert('New passwords do not match')
    return
  }
  
  // Handle password change
  console.log('Changing password:', passwordForm.value)
  passwordForm.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  }
}

const deleteAccount = () => {
  // Handle account deletion
  console.log('Deleting account')
  showDeleteConfirm.value = false
}
</script> 