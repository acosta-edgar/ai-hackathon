<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profile</h1>
      <p class="mt-2 text-gray-600 dark:text-gray-300">Manage your personal information and preferences</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Profile Information -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personal Information</h2>
          <form @submit.prevent="savePersonalInfo" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                <input
                  v-model="profile.firstName"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                <input
                  v-model="profile.lastName"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                >
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
              <input
                v-model="profile.email"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
              <input
                v-model="profile.phone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location</label>
              <input
                v-model="profile.location"
                type="text"
                placeholder="City, State"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
            </div>
            <div class="flex justify-end">
              <button
                type="submit"
                class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Professional Information -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Professional Information</h2>
          <form @submit.prevent="saveProfessionalInfo" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Headline</label>
              <input
                v-model="profile.headline"
                type="text"
                placeholder="e.g., Senior Frontend Developer"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Summary</label>
              <textarea
                v-model="profile.summary"
                rows="4"
                placeholder="Brief description of your experience and skills..."
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Skills</label>
              <input
                v-model="profile.skills"
                type="text"
                placeholder="JavaScript, Vue.js, React, Node.js (comma separated)"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Experience Level</label>
              <select
                v-model="profile.experienceLevel"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Select experience level</option>
                <option value="entry">Entry Level (0-2 years)</option>
                <option value="mid">Mid Level (3-5 years)</option>
                <option value="senior">Senior Level (6-8 years)</option>
                <option value="lead">Lead/Manager (8+ years)</option>
              </select>
            </div>
            <div class="flex justify-end">
              <button
                type="submit"
                class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Resume Upload -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resume</h2>
          <div class="space-y-4">
            <div v-if="profile.resumeUrl" class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-md">
              <div class="flex items-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">Current Resume</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Uploaded on {{ formatDate(profile.resumeUploadDate) }}</p>
                </div>
              </div>
              <button
                @click="downloadResume"
                class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium"
              >
                Download
              </button>
            </div>
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-md p-6 text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
              <div class="mt-4">
                <label class="cursor-pointer">
                  <span class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">Upload a new resume</span>
                  <input type="file" class="sr-only" accept=".pdf,.doc,.docx" @change="uploadResume">
                </label>
                <p class="text-sm text-gray-500 dark:text-gray-400">PDF, DOC, or DOCX up to 10MB</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Profile Picture -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Picture</h2>
          <div class="text-center">
            <div class="relative inline-block">
              <img
                :src="profile.avatarUrl || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(profile.firstName + ' ' + profile.lastName) + '&background=0D8ABC&color=fff'"
                alt="Profile"
                class="w-24 h-24 rounded-full mx-auto"
              >
              <button
                @click="$refs.avatarInput.click()"
                class="absolute bottom-0 right-0 bg-primary-600 text-white rounded-full p-2 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </button>
              <input
                ref="avatarInput"
                type="file"
                class="sr-only"
                accept="image/*"
                @change="uploadAvatar"
              >
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Click to upload a new photo</p>
          </div>
        </div>

        <!-- Job Preferences -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Job Preferences</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preferred Job Type</label>
              <select
                v-model="profile.preferredJobType"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Any</option>
                <option value="full-time">Full Time</option>
                <option value="part-time">Part Time</option>
                <option value="contract">Contract</option>
                <option value="freelance">Freelance</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Remote Preference</label>
              <select
                v-model="profile.remotePreference"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Any</option>
                <option value="remote">Remote Only</option>
                <option value="hybrid">Hybrid</option>
                <option value="onsite">On-site</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Salary Range</label>
              <div class="grid grid-cols-2 gap-2">
                <input
                  v-model="profile.minSalary"
                  type="number"
                  placeholder="Min"
                  class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                >
                <input
                  v-model="profile.maxSalary"
                  type="number"
                  placeholder="Max"
                  class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                >
              </div>
            </div>
            <button
              @click="savePreferences"
              class="w-full px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              Save Preferences
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const profile = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  location: '',
  headline: '',
  summary: '',
  skills: '',
  experienceLevel: '',
  avatarUrl: '',
  resumeUrl: '',
  resumeUploadDate: null,
  preferredJobType: '',
  remotePreference: '',
  minSalary: '',
  maxSalary: ''
})

const savePersonalInfo = () => {
  // Save personal information
  console.log('Saving personal info:', profile.value)
}

const saveProfessionalInfo = () => {
  // Save professional information
  console.log('Saving professional info:', profile.value)
}

const savePreferences = () => {
  // Save job preferences
  console.log('Saving preferences:', profile.value)
}

const uploadAvatar = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Handle avatar upload
    console.log('Uploading avatar:', file)
  }
}

const uploadResume = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Handle resume upload
    console.log('Uploading resume:', file)
    profile.value.resumeUploadDate = new Date()
  }
}

const downloadResume = () => {
  // Handle resume download
  console.log('Downloading resume')
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}

onMounted(async () => {
  // Load profile data
  profile.value = {
    firstName: 'John',
    lastName: 'Doe',
    email: 'john.doe@example.com',
    phone: '+1 (555) 123-4567',
    location: 'San Francisco, CA',
    headline: 'Senior Frontend Developer',
    summary: 'Experienced frontend developer with 5+ years building modern web applications using Vue.js, React, and TypeScript.',
    skills: 'JavaScript, Vue.js, React, TypeScript, Node.js, CSS, HTML',
    experienceLevel: 'senior',
    avatarUrl: '',
    resumeUrl: '/resume.pdf',
    resumeUploadDate: new Date('2024-01-01'),
    preferredJobType: 'full-time',
    remotePreference: 'hybrid',
    minSalary: '120000',
    maxSalary: '180000'
  }
})
</script> 