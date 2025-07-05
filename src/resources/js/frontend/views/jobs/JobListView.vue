<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useJobStore } from '@/stores/job'
import { useToast } from 'vue-toastification'
import JobCard from '@/components/jobs/JobCard.vue'
import JobFilters from '@/components/jobs/JobFilters.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const route = useRoute()
const router = useRouter()
const jobStore = useJobStore()
const toast = useToast()

// State
const isLoading = ref(false)
const error = ref(null)
const page = ref(1)
const perPage = ref(10)
const selectedJob = ref(null)

// Computed
const jobs = computed(() => jobStore.jobs)
const totalJobs = computed(() => jobStore.total)
const totalPages = computed(() => Math.ceil(totalJobs.value / perPage.value))
const hasJobs = computed(() => jobs.value.length > 0)

// Methods
const fetchJobs = async () => {
  isLoading.value = true
  error.value = null
  
  try {
    await jobStore.fetchJobs({
      page: page.value,
      per_page: perPage.value,
      ...route.query
    })
  } catch (err) {
    console.error('Failed to fetch jobs:', err)
    error.value = 'Failed to load jobs. Please try again.'
    toast.error(error.value)
  } finally {
    isLoading.value = false
  }
}

const handlePageChange = (newPage) => {
  page.value = newPage
  router.push({ 
    query: { 
      ...route.query, 
      page: newPage 
    } 
  })
}

const handleFilterChange = (filters) => {
  page.value = 1 // Reset to first page on filter change
  router.push({ 
    query: { 
      ...filters,
      page: 1
    } 
  })
}

const handleJobClick = (job) => {
  selectedJob.value = job
  // Update URL without navigation
  router.push({ 
    name: 'job-detail', 
    params: { id: job.id },
    query: route.query
  })
}

// Lifecycle hooks
onMounted(() => {
  fetchJobs()
})

// Watch for route changes
watch(() => route.query, (newQuery) => {
  page.value = parseInt(newQuery.page) || 1
  fetchJobs()
}, { immediate: true })
</script>

<template>
  <div class="job-list-view">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Job Matches
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          {{ totalJobs }} jobs found
        </p>
      </div>
      
      <div class="mt-4 md:mt-0">
        <button
          @click="$refs.filterPanel.toggle()"
          class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        >
          <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
          </svg>
          Filters
        </button>
      </div>
    </div>

    <!-- Main content -->
    <div class="flex flex-col lg:flex-row gap-6">
      <!-- Filters panel (mobile) -->
      <div class="lg:hidden">
        <JobFilters 
          ref="filterPanel"
          :filters="route.query"
          @filter-change="handleFilterChange"
          class="mb-6"
        />
      </div>

      <!-- Filters panel (desktop) -->
      <div class="hidden lg:block w-72 flex-shrink-0">
        <JobFilters
          :filters="route.query"
          @filter-change="handleFilterChange"
          class="sticky top-6"
        />
      </div>

      <!-- Job list -->
      <div class="flex-1">
        <!-- Loading state -->
        <div v-if="isLoading && !hasJobs" class="flex justify-center items-center py-12">
          <LoadingSpinner size="lg" />
        </div>

        <!-- Error state -->
        <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 p-4 rounded-md">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                Error loading jobs
              </h3>
              <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                <p>{{ error }}</p>
              </div>
              <div class="mt-4">
                <button
                  @click="fetchJobs"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 dark:text-red-200 dark:bg-red-900/50 dark:hover:bg-red-800/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                  Try again
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="!hasJobs" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No jobs found</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Try adjusting your search or filter to find what you're looking for.
          </p>
          <div class="mt-6">
            <button
              @click="handleFilterChange({})"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              Clear all filters
            </button>
          </div>
        </div>

        <!-- Job list -->
        <div v-else class="space-y-4">
          <JobCard
            v-for="job in jobs"
            :key="job.id"
            :job="job"
            :is-selected="selectedJob?.id === job.id"
            @click="handleJobClick(job)"
            class="cursor-pointer transition-shadow hover:shadow-lg"
          />

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="mt-8 flex items-center justify-between">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                  Showing <span class="font-medium">{{ (page - 1) * perPage + 1 }}</span>
                  to <span class="font-medium">{{ Math.min(page * perPage, totalJobs) }}</span>
                  of <span class="font-medium">{{ totalJobs }}</span> results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="handlePageChange(page - 1)"
                    :disabled="page === 1"
                    :class="{
                      'opacity-50 cursor-not-allowed': page === 1,
                      'hover:bg-gray-50 dark:hover:bg-gray-800': page > 1,
                    }"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-300"
                  >
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  
                  <button
                    v-for="pageNum in totalPages"
                    :key="pageNum"
                    @click="handlePageChange(pageNum)"
                    :class="{
                      'z-10 bg-primary-50 dark:bg-primary-900/50 border-primary-500 dark:border-primary-600 text-primary-600 dark:text-primary-200': pageNum === page,
                      'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700': pageNum !== page,
                    }"
                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                  >
                    {{ pageNum }}
                  </button>
                  
                  <button
                    @click="handlePageChange(page + 1)"
                    :disabled="page === totalPages"
                    :class="{
                      'opacity-50 cursor-not-allowed': page === totalPages,
                      'hover:bg-gray-50 dark:hover:bg-gray-800': page < totalPages,
                    }"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-300"
                  >
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.job-list-view {
  @apply py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto;
}

/* Smooth scrolling for mobile */
@media (max-width: 1023px) {
  .job-list-view {
    scroll-behavior: smooth;
    scroll-padding-top: 1.5rem;
  }
}
</style>
