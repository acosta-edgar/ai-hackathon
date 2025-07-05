<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
          Job Details
        </h2>
      </div>
    </template>

    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
      <div v-if="loading" class="p-6">
        <div class="animate-pulse space-y-4">
          <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
        </div>
      </div>

      <div v-else-if="job" class="px-4 py-5 sm:px-6">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
              {{ job.title }}
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
              {{ job.company }} • {{ job.location }}
            </p>
          </div>
          <div class="flex space-x-3">
            <button
              type="button"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              Apply Now
            </button>
          </div>
        </div>

        <div class="mt-5 border-t border-gray-200 dark:border-gray-700 pt-5">
          <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Job Type
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                {{ job.type || 'N/A' }}
              </dd>
            </div>
            <div class="sm:col-span-1">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Salary Range
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                {{ job.salary || 'Not specified' }}
              </dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Description
              </dt>
              <dd 
                class="mt-1 text-sm text-gray-900 dark:text-white prose dark:prose-invert max-w-none"
                v-html="job.description || 'No description provided'"
              ></dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Requirements
              </dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                <ul class="list-disc pl-5 space-y-1">
                  <li v-for="(requirement, index) in job.requirements" :key="index">
                    {{ requirement }}
                  </li>
                  <li v-if="!job.requirements || job.requirements.length === 0">
                    No specific requirements listed
                  </li>
                </ul>
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <div v-else class="p-6 text-center">
        <p class="text-gray-500 dark:text-gray-400">Job not found</p>
        <router-link
          to="/jobs"
          class="mt-4 inline-flex items-center text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
        >
          Back to Jobs
        </router-link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'

const route = useRoute()
const job = ref(null)
const loading = ref(true)
const error = ref(null)

// Mock data - replace with actual API call
const fetchJobDetails = async (id) => {
  try {
    loading.value = true
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 500))
    
    // Mock data - replace with actual API call
    const mockJobs = [
      {
        id: '1',
        title: 'Senior Frontend Developer',
        company: 'TechCorp',
        location: 'Remote',
        type: 'Full-time',
        salary: '$100,000 - $140,000',
        description: '<p>We are looking for an experienced Frontend Developer to join our team. You will be responsible for building user interfaces and implementing features according to design specifications.</p>',
        requirements: [
          '5+ years of experience with Vue.js or React',
          'Strong JavaScript/TypeScript skills',
          'Experience with state management (Vuex/Pinia/Redux)',
          'Familiarity with RESTful APIs',
          'Experience with testing frameworks (Jest, Vitest)'
        ]
      },
      {
        id: '2',
        title: 'Backend Engineer',
        company: 'DataSystems',
        location: 'New York, NY',
        type: 'Full-time',
        salary: '$120,000 - $160,000',
        description: '<p>Join our backend engineering team to build scalable and reliable services.</p>',
        requirements: [
          'Experience with Node.js and Express/NestJS',
          'Database design and optimization',
          'API development',
          'Cloud services experience'
        ]
      }
    ]
    
    const foundJob = mockJobs.find(job => job.id === id)
    if (foundJob) {
      job.value = foundJob
    } else {
      error.value = 'Job not found'
    }
  } catch (err) {
    error.value = 'Failed to load job details. Please try again later.'
    console.error('Error fetching job details:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (route.params.id) {
    fetchJobDetails(route.params.id)
  } else {
    loading.value = false
  }
})
</script>
