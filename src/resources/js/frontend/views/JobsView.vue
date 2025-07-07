<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Job Listings</h1>
      <p class="mt-1 text-sm text-gray-600">
        Find your next career opportunity from our curated list of jobs
      </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
      <!-- Mobile Filters -->
      <div class="lg:hidden">
        <button
          @click="showMobileFilters = true"
          class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900"
        >
          <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
          </svg>
          Filters
        </button>

        <!-- Mobile filter dialog -->
        <TransitionRoot as="template" :show="showMobileFilters">
          <Dialog as="div" class="fixed inset-0 z-40 flex lg:hidden" @close="showMobileFilters = false">
            <TransitionChild
              as="template"
              enter="transition-opacity ease-linear duration-300"
              enter-from="opacity-0"
              enter-to="opacity-100"
              leave="transition-opacity ease-linear duration-300"
              leave-from="opacity-100"
              leave-to="opacity-0"
            >
              <DialogOverlay class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <TransitionChild
              as="template"
              enter="transition ease-in-out duration-300 transform"
              enter-from="translate-x-full"
              enter-to="translate-x-0"
              leave="transition ease-in-out duration-300 transform"
              leave-from="translate-x-0"
              leave-to="translate-x-full"
            >
              <div class="relative ml-auto flex h-full w-full max-w-xs flex-col overflow-y-auto bg-white py-4 pb-12 shadow-xl">
                <div class="flex items-center justify-between px-4">
                  <h2 class="text-lg font-medium text-gray-900">Filters</h2>
                  <button
                    type="button"
                    class="-mr-2 flex h-10 w-10 items-center justify-center rounded-md bg-white p-2 text-gray-400"
                    @click="showMobileFilters = false"
                  >
                    <span class="sr-only">Close menu</span>
                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                  </button>
                </div>

                <!-- Mobile Filters -->
                <JobFilters 
                  :filters="filters" 
                  @update:filters="handleFiltersUpdate"
                  @reset="resetFilters"
                  class="px-4 mt-4"
                />
              </div>
            </TransitionChild>
          </Dialog>
        </TransitionRoot>
      </div>

      <!-- Desktop Filters -->
      <div class="hidden lg:block w-72 flex-shrink-0">
        <JobFilters 
          :filters="filters" 
          @update:filters="handleFiltersUpdate"
          @reset="resetFilters"
        />
      </div>

      <!-- Job List -->
      <div class="flex-1">
        <!-- Search and Sort -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
          <div class="relative w-full sm:max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
            </div>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search jobs..."
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              @keyup.enter="fetchJobs"
            />
          </div>
          
          <div class="flex items-center w-full sm:w-auto">
            <div class="flex items-center">
              <label for="sort" class="mr-2 text-sm font-medium text-gray-700">Sort by:</label>
              <select
                id="sort"
                v-model="filters.sort"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                @change="fetchJobs"
              >
                <option value="relevance">Relevance</option>
                <option value="date_desc">Newest</option>
                <option value="date_asc">Oldest</option>
                <option value="match_desc">Best Match</option>
                <option value="salary_desc">Salary (High to Low)</option>
                <option value="salary_asc">Salary (Low to High)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Active Filters -->
        <div v-if="hasActiveFilters" class="mb-6">
          <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-gray-700">Filters:</span>
            
            <template v-for="(value, key) in activeFilters" :key="key">
              <span 
                v-if="value && value.length > 0"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
              >
                {{ getFilterLabel(key) }}: {{ formatFilterValue(key, value) }}
                <button 
                  type="button" 
                  class="flex-shrink-0 ml-1.5 inline-flex text-blue-400 hover:text-blue-600 focus:outline-none"
                  @click="removeFilter(key)"
                >
                  <span class="sr-only">Remove filter</span>
                  <XMarkIcon class="h-3.5 w-3.5" aria-hidden="true" />
                </button>
              </span>
            </template>

            <button 
              type="button" 
              class="ml-2 text-sm font-medium text-blue-600 hover:text-blue-500"
              @click="resetFilters"
            >
              Clear all
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="space-y-6">
          <!-- Skeleton loaders -->
          <div v-for="i in 5" :key="`skeleton-${i}`" class="bg-white rounded-lg shadow p-6 animate-pulse">
            <div class="flex items-start space-x-4">
              <div class="h-12 w-12 bg-gray-200 rounded-md"></div>
              <div class="flex-1 space-y-3">
                <div class="h-5 bg-gray-200 rounded w-3/4"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                <div class="h-4 bg-gray-200 rounded w-1/4"></div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Error state -->
        <div v-else-if="error" class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400" aria-hidden="true" />
            </div>
            <div class="ml-3">
              <p class="text-sm text-yellow-700">
                {{ error }}
                <button 
                  @click="fetchJobs" 
                  class="font-medium text-yellow-700 hover:text-yellow-600 underline"
                >
                  Try again
                </button>
              </p>
            </div>
          </div>
        </div>

        <!-- Job List -->
        <div v-else-if="jobs.length > 0" class="space-y-6">
          <JobCard 
            v-for="job in jobs" 
            :key="job.id" 
            :job="job" 
            @save="handleSaveJob(job.id, $event)"
            @apply="handleApplyJob(job.id, $event)"
            @not-interested="handleNotInterested(job.id)"
          />
          
          <!-- Pagination -->
          <div v-if="meta.last_page > 1" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            <div class="flex flex-1 justify-between sm:hidden">
              <button 
                @click="changePage(meta.current_page - 1)" 
                :disabled="meta.current_page === 1"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <button 
                @click="changePage(meta.current_page + 1)" 
                :disabled="meta.current_page === meta.last_page"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing <span class="font-medium">{{ meta.from || 0 }}</span> to <span class="font-medium">{{ meta.to || 0 }}</span> of
                  {{ ' ' }}
                  <span class="font-medium">{{ meta.total }}</span> results
                </p>
              </div>
              <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                  <button
                    @click="changePage(meta.current_page - 1)"
                    :disabled="meta.current_page === 1"
                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                    :class="{ 'opacity-50 cursor-not-allowed': meta.current_page === 1 }"
                  >
                    <span class="sr-only">Previous</span>
                    <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                  </button>
                  
                  <template v-for="page in pages">
                    <button
                      v-if="page === '...'"
                      :key="`ellipsis-${page}`"
                      class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0"
                      disabled
                    >
                      {{ page }}
                    </button>
                    <button
                      v-else
                      :key="`page-${page}`"
                      @click="changePage(page)"
                      class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                      :class="[
                        page === meta.current_page 
                          ? 'bg-indigo-600 text-white focus-visible:outline-indigo-600' 
                          : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0'
                      ]"
                    >
                      {{ page }}
                    </button>
                  </template>
                  
                  <button
                    @click="changePage(meta.current_page + 1)"
                    :disabled="meta.current_page === meta.last_page"
                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"
                    :class="{ 'opacity-50 cursor-not-allowed': meta.current_page === meta.last_page }"
                  >
                    <span class="sr-only">Next</span>
                    <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Empty state -->
        <div v-else class="text-center py-12">
          <svg
            class="mx-auto h-12 w-12 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              vector-effect="non-scaling-stroke"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No jobs found</h3>
          <p class="mt-1 text-sm text-gray-500">
            Try adjusting your search or filter to find what you're looking for.
          </p>
          <div class="mt-6">
            <button
              @click="resetFilters"
              class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
              <ArrowPathIcon class="-ml-0.5 mr-1.5 h-4 w-4" aria-hidden="true" />
              Reset filters
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Dialog, DialogOverlay, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { 
  XMarkIcon, 
  MagnifyingGlassIcon, 
  ChevronLeftIcon, 
  ChevronRightIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

import JobCard from '@/components/jobs/JobCard.vue'
import JobFilters from '@/components/jobs/JobFilters.vue'
import jobService from '@/services/jobService'

export default {
  name: 'JobsView',
  components: {
    JobCard,
    JobFilters,
    Dialog,
    DialogOverlay,
    TransitionChild,
    TransitionRoot,
    XMarkIcon,
    MagnifyingGlassIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ArrowPathIcon,
    ExclamationTriangleIcon
  },
  
  setup() {
    const route = useRoute()
    const router = useRouter()
    
    const jobs = ref([])
    const loading = ref(true)
    const error = ref(null)
    const showMobileFilters = ref(false)
    const meta = ref({
      current_page: 1,
      from: 0,
      last_page: 1,
      per_page: 10,
      to: 0,
      total: 0
    })
    
    // Initialize filters from route query or use defaults
    const filters = ref({
      search: route.query.search || '',
      location: route.query.location || '',
      job_type: route.query.job_type ? route.query.job_type.split(',') : [],
      experience_level: route.query.experience_level ? route.query.experience_level.split(',') : [],
      salary_min: route.query.salary_min || '',
      salary_max: route.query.salary_max || '',
      remote: route.query.remote === 'true' || false,
      posted_within: route.query.posted_within || '',
      sort: route.query.sort || 'relevance',
      page: parseInt(route.query.page) || 1
    })

    // Computed properties
    const hasActiveFilters = computed(() => {
      return Object.entries(filters.value).some(([key, value]) => {
        if (key === 'page') return false
        if (Array.isArray(value)) return value.length > 0
        return value !== '' && value !== false
      })
    })

    const activeFilters = computed(() => {
      const { page, ...rest } = filters.value
      return Object.entries(rest).reduce((acc, [key, value]) => {
        if ((Array.isArray(value) && value.length > 0) || 
            (typeof value === 'string' && value !== '') ||
            (typeof value === 'boolean' && value)) {
          acc[key] = value
        }
        return acc
      }, {})
    })

    const pages = computed(() => {
      if (!meta.value || meta.value.last_page <= 1) return []
      
      const current = meta.value.current_page
      const last = meta.value.last_page
      const delta = 1
      const left = current - delta
      const right = current + delta + 1
      const range = []
      const rangeWithDots = []
      let l
      
      for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= left && i < right)) {
          range.push(i)
        }
      }
      
      for (let i of range) {
        if (l) {
          if (i - l > 1) {
            rangeWithDots.push('...')
          }
        }
        rangeWithDots.push(i)
        l = i
      }
      
      return rangeWithDots
    })

    // Methods
    const fetchJobs = async () => {
      loading.value = true
      error.value = null
      
      try {
        // Prepare filters for API
        const { page, per_page, ...apiFilters } = filters.value
        
        // Remove empty filters
        Object.keys(apiFilters).forEach(key => {
          if (Array.isArray(apiFilters[key]) && apiFilters[key].length === 0) {
            delete apiFilters[key]
          } else if (apiFilters[key] === '' || apiFilters[key] === false) {
            delete apiFilters[key]
          }
        })
        
        // Update URL with current filters
        const queryParams = {
          ...apiFilters,
          page: filters.value.page || 1,
          per_page: filters.value.per_page || 10
        }
        
        // Only update URL if query params changed
        if (JSON.stringify(route.query) !== JSON.stringify(queryParams)) {
          router.push({ query: queryParams })
        }
        
        // Call the job service to search for jobs
        const response = await jobService.searchJobs(
          {
            query: apiFilters.search || '',
            location: apiFilters.location || '',
            country: 'us', // Default country
          },
          queryParams.page,
          queryParams.per_page,
          apiFilters
        )
        
        // Update jobs and pagination
        jobs.value = response.data
        
        // Update pagination meta
        if (response.meta) {
          meta.value = {
            current_page: response.meta.current_page || 1,
            from: response.meta.from || 0,
            last_page: response.meta.last_page || 1,
            per_page: response.meta.per_page || 10,
            to: response.meta.to || 0,
            total: response.meta.total || 0
          }
        }
        
      } catch (err) {
        console.error('Error fetching jobs:', err)
        error.value = 'Failed to load jobs. Please try again later.'
        
        // Reset data on error
        jobs.value = []
        meta.value = {
          current_page: 1,
          from: 0,
          last_page: 1,
          per_page: 10,
          to: 0,
          total: 0
        }
      } finally {
        loading.value = false
      }
    }
    
    const handleFiltersUpdate = (updatedFilters) => {
      filters.value = { ...filters.value, ...updatedFilters, page: 1 }
      fetchJobs()
    }
    
    const resetFilters = () => {
      filters.value = {
        search: '',
        location: '',
        job_type: [],
        experience_level: [],
        salary_min: '',
        salary_max: '',
        remote: false,
        posted_within: '',
        sort: 'relevance',
        page: 1
      }
      
      router.push({ query: { page: 1 } })
      fetchJobs()
    }
    
    const removeFilter = (key) => {
      if (Array.isArray(filters.value[key])) {
        filters.value[key] = []
      } else if (typeof filters.value[key] === 'boolean') {
        filters.value[key] = false
      } else {
        filters.value[key] = ''
      }
      
      // Reset to first page when filters change
      filters.value.page = 1
      
      fetchJobs()
    }
    
    const changePage = (page) => {
      if (page < 1 || page > meta.value.last_page) return
      
      filters.value.page = page
      fetchJobs()
      
      // Scroll to top of the list
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
    
    const getFilterLabel = (key) => {
      const labels = {
        search: 'Search',
        location: 'Location',
        job_type: 'Job Type',
        experience_level: 'Experience',
        salary_min: 'Min Salary',
        salary_max: 'Max Salary',
        remote: 'Remote',
        posted_within: 'Posted',
        sort: 'Sort By'
      }
      
      return labels[key] || key.replace(/_/g, ' ')
    }
    
    const formatFilterValue = (key, value) => {
      if (key === 'job_type' || key === 'experience_level') {
        return Array.isArray(value) ? value.join(', ') : value
      }
      
      if (key === 'remote') {
        return 'Remote Only'
      }
      
      if (key === 'posted_within') {
        const options = {
          '24h': 'Last 24 hours',
          '7d': 'Last 7 days',
          '30d': 'Last 30 days',
          '90d': 'Last 3 months'
        }
        return options[value] || value
      }
      
      return value
    }
    
    // Watch for route changes to update filters
    watch(() => route.query, (newQuery) => {
      // Only update if the query params actually changed
      const queryChanged = Object.keys(newQuery).some(key => {
        return JSON.stringify(newQuery[key]) !== JSON.stringify(filters.value[key])
      })
      
      if (queryChanged) {
        filters.value = {
          search: newQuery.search || '',
          location: newQuery.location || '',
          job_type: newQuery.job_type ? newQuery.job_type.split(',') : [],
          experience_level: newQuery.experience_level ? newQuery.experience_level.split(',') : [],
          salary_min: newQuery.salary_min || '',
          salary_max: newQuery.salary_max || '',
          remote: newQuery.remote === 'true',
          posted_within: newQuery.posted_within || '',
          sort: newQuery.sort || 'relevance',
          page: parseInt(newQuery.page) || 1,
          per_page: parseInt(newQuery.per_page) || 10
        }
        
        fetchJobs()
      }
    }, { immediate: true, deep: true })
    
    // Initial fetch
    onMounted(() => {
      fetchJobs()
    })
    
    return {
      jobs,
      loading,
      error,
      meta,
      pages,
      filters,
      hasActiveFilters,
      activeFilters,
      showMobileFilters,
      fetchJobs,
      handleFiltersUpdate,
      resetFilters,
      removeFilter,
      changePage,
      getFilterLabel,
      formatFilterValue
    }
  }
}
</script>

<style scoped>
/* Add any additional styles here */
</style>
