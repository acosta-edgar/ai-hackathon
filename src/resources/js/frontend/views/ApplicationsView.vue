<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Applications</h1>
      <p class="mt-2 text-gray-600 dark:text-gray-300">Track your job applications and their status</p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
      <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-0">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search applications..."
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
          >
        </div>
        <div class="w-48">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="">All Status</option>
            <option value="applied">Applied</option>
            <option value="reviewing">Under Review</option>
            <option value="interviewing">Interviewing</option>
            <option value="offered">Offered</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div class="w-48">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
          <select
            v-model="filters.sortBy"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
          >
            <option value="date">Date Applied</option>
            <option value="company">Company</option>
            <option value="position">Position</option>
            <option value="status">Status</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Applications List -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Your Applications</h2>
      </div>
      
      <div v-if="loading" class="p-6 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading applications...</p>
      </div>

      <div v-else-if="filteredApplications.length === 0" class="p-6 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No applications found</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by applying to some jobs!</p>
        <div class="mt-6">
          <router-link
            to="/jobs"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Browse Jobs
          </router-link>
        </div>
      </div>

      <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
        <div
          v-for="application in filteredApplications"
          :key="application.id"
          class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                  {{ application.position }}
                </h3>
                <span
                  :class="getStatusBadgeClass(application.status)"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                >
                  {{ getStatusText(application.status) }}
                </span>
              </div>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ application.company }}
              </p>
              <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                <span>Applied {{ formatDate(application.appliedDate) }}</span>
                <span>•</span>
                <span>{{ application.location }}</span>
                <span>•</span>
                <span>{{ application.salary }}</span>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="viewApplication(application)"
                class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium"
              >
                View Details
              </button>
              <button
                @click="withdrawApplication(application)"
                class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium"
              >
                Withdraw
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const loading = ref(true)
const applications = ref([])

const filters = ref({
  search: '',
  status: '',
  sortBy: 'date'
})

const filteredApplications = computed(() => {
  let filtered = applications.value

  // Filter by search
  if (filters.value.search) {
    const searchTerm = filters.value.search.toLowerCase()
    filtered = filtered.filter(app => 
      app.position.toLowerCase().includes(searchTerm) ||
      app.company.toLowerCase().includes(searchTerm)
    )
  }

  // Filter by status
  if (filters.value.status) {
    filtered = filtered.filter(app => app.status === filters.value.status)
  }

  // Sort
  filtered.sort((a, b) => {
    switch (filters.value.sortBy) {
      case 'date':
        return new Date(b.appliedDate) - new Date(a.appliedDate)
      case 'company':
        return a.company.localeCompare(b.company)
      case 'position':
        return a.position.localeCompare(b.position)
      case 'status':
        return a.status.localeCompare(b.status)
      default:
        return 0
    }
  })

  return filtered
})

const getStatusBadgeClass = (status) => {
  const classes = {
    applied: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    reviewing: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    interviewing: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    offered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
  }
  return classes[status] || classes.applied
}

const getStatusText = (status) => {
  const texts = {
    applied: 'Applied',
    reviewing: 'Under Review',
    interviewing: 'Interviewing',
    offered: 'Offered',
    rejected: 'Rejected'
  }
  return texts[status] || 'Applied'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const viewApplication = (application) => {
  // Navigate to application details
  console.log('View application:', application)
}

const withdrawApplication = (application) => {
  if (confirm('Are you sure you want to withdraw this application?')) {
    // Handle withdrawal
    console.log('Withdraw application:', application)
  }
}

onMounted(async () => {
  // Simulate loading applications
  setTimeout(() => {
    applications.value = [
      {
        id: 1,
        position: 'Senior Frontend Developer',
        company: 'TechCorp',
        location: 'San Francisco, CA',
        salary: '$120k - $150k',
        status: 'interviewing',
        appliedDate: '2024-01-15'
      },
      {
        id: 2,
        position: 'Full Stack Engineer',
        company: 'StartupXYZ',
        location: 'Remote',
        salary: '$100k - $130k',
        status: 'reviewing',
        appliedDate: '2024-01-12'
      },
      {
        id: 3,
        position: 'React Developer',
        company: 'BigTech Inc',
        location: 'New York, NY',
        salary: '$110k - $140k',
        status: 'applied',
        appliedDate: '2024-01-10'
      },
      {
        id: 4,
        position: 'Vue.js Developer',
        company: 'SmallStartup',
        location: 'Austin, TX',
        salary: '$90k - $120k',
        status: 'rejected',
        appliedDate: '2024-01-08'
      }
    ]
    loading.value = false
  }, 1000)
})
</script> 