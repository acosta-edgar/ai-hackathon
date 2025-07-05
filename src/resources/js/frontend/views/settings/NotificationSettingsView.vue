<template>
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Notification Settings</h2>
    
    <div class="space-y-6">
      <!-- Email Notifications -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Email Notifications</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Job Matches</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Get notified when we find jobs that match your profile</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="notifications.email.jobMatches"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Application Updates</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Receive updates about your job applications</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="notifications.email.applicationUpdates"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Weekly Digest</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Get a weekly summary of your job search activity</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="notifications.email.weeklyDigest"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
        </div>
      </div>

      <!-- Push Notifications -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Push Notifications</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">New Job Alerts</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Get instant notifications for new job opportunities</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="notifications.push.newJobs"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Application Status</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Get notified when your application status changes</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="notifications.push.applicationStatus"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Interview Reminders</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Get reminded about upcoming interviews</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="notifications.push.interviewReminders"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
        </div>
      </div>

      <!-- Notification Frequency -->
      <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Notification Frequency</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Job Alert Frequency</label>
            <select
              v-model="notifications.frequency.jobAlerts"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            >
              <option value="instant">Instant</option>
              <option value="daily">Daily Digest</option>
              <option value="weekly">Weekly Digest</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Application Update Frequency</label>
            <select
              v-model="notifications.frequency.applicationUpdates"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
            >
              <option value="instant">Instant</option>
              <option value="daily">Daily Digest</option>
              <option value="weekly">Weekly Digest</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Quiet Hours -->
      <div>
        <h3 class="text-md font-medium text-gray-900 dark:text-white mb-4">Quiet Hours</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">Enable Quiet Hours</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Pause notifications during specific hours</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input
                v-model="notifications.quietHours.enabled"
                type="checkbox"
                class="sr-only peer"
              >
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
            </label>
          </div>
          <div v-if="notifications.quietHours.enabled" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Time</label>
              <input
                v-model="notifications.quietHours.startTime"
                type="time"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Time</label>
              <input
                v-model="notifications.quietHours.endTime"
                type="time"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="flex justify-end pt-6">
        <button
          @click="saveNotifications"
          class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          Save Notification Settings
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const notifications = ref({
  email: {
    jobMatches: true,
    applicationUpdates: true,
    weeklyDigest: false
  },
  push: {
    newJobs: true,
    applicationStatus: true,
    interviewReminders: true
  },
  frequency: {
    jobAlerts: 'instant',
    applicationUpdates: 'instant'
  },
  quietHours: {
    enabled: false,
    startTime: '22:00',
    endTime: '08:00'
  }
})

const saveNotifications = () => {
  // Save notification settings
  console.log('Saving notification settings:', notifications.value)
}
</script> 