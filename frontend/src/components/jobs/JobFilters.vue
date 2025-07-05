<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { XMarkIcon, FunnelIcon, AdjustmentsHorizontalIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({})
  },
  showTitle: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['filter-change'])

const route = useRoute()
const isOpen = ref(false)
const isMobile = ref(false)

// Form fields
const form = ref({
  q: '',
  location: '',
  job_type: '',
  experience_level: '',
  salary_min: '',
  salary_max: '',
  is_remote: false,
  posted_within: '',
  skills: [],
  sort_by: 'relevance',
  ...props.filters
})

// Available options
const jobTypes = [
  { value: 'full-time', label: 'Full-time' },
  { value: 'part-time', label: 'Part-time' },
  { value: 'contract', label: 'Contract' },
  { value: 'temporary', label: 'Temporary' },
  { value: 'internship', label: 'Internship' },
  { value: 'freelance', label: 'Freelance' }
]

experienceLevels = [
  { value: 'internship', label: 'Internship' },
  { value: 'entry', label: 'Entry Level' },
  { value: 'associate', label: 'Associate' },
  { value: 'mid-senior', label: 'Mid-Senior' },
  { value: 'director', label: 'Director' },
  { value: 'executive', label: 'Executive' }
]

const postedWithinOptions = [
  { value: '1', label: 'Last 24 hours' },
  { value: '3', label: 'Last 3 days' },
  { value: '7', label: 'Last week' },
  { value: '30', label: 'Last month' },
  { value: '90', label: 'Last 3 months' }
]

const sortOptions = [
  { value: 'relevance', label: 'Relevance' },
  { value: 'date', label: 'Most Recent' },
  { value: 'salary_high', label: 'Salary: High to Low' },
  { value: 'salary_low', label: 'Salary: Low to High' },
  { value: 'match_score', label: 'Best Matches' }
]

// Computed properties
const activeFilterCount = computed(() => {
  return Object.keys(form.value).filter(key => {
    const val = form.value[key]
    return val !== null && val !== '' && val !== false && (!Array.isArray(val) || val.length > 0)
  }).length - 1 // Subtract 1 for sort_by which is always set
})

// Methods
const togglePanel = () => {
  isOpen.value = !isOpen.value
}

const resetFilters = () => {
  form.value = {
    q: '',
    location: '',
    job_type: '',
    experience_level: '',
    salary_min: '',
    salary_max: '',
    is_remote: false,
    posted_within: '',
    skills: [],
    sort_by: 'relevance'
  }
  applyFilters()
}

const applyFilters = () => {
  // Clean up empty values
  const cleanedFilters = {}
  Object.keys(form.value).forEach(key => {
    const val = form.value[key]
    if (val !== null && val !== '' && val !== false && (!Array.isArray(val) || val.length > 0)) {
      cleanedFilters[key] = val
    }
  })
  
  emit('filter-change', cleanedFilters)
  
  // Close the panel on mobile after applying filters
  if (isMobile.value) {
    isOpen.value = false
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Enter') {
    applyFilters()
  }
}

// Watch for route changes to update form
watch(() => route.query, (newQuery) => {
  Object.keys(newQuery).forEach(key => {
    if (key in form.value) {
      // Handle array values (like skills)
      if (Array.isArray(form.value[key])) {
        form.value[key] = Array.isArray(newQuery[key]) 
          ? newQuery[key] 
          : newQuery[key].split(',')
      } 
      // Handle boolean values
      else if (typeof form.value[key] === 'boolean') {
        form.value[key] = newQuery[key] === 'true'
      }
      // Handle other values
      else {
        form.value[key] = newQuery[key]
      }
    }
  })
}, { immediate: true })

// Check if mobile
const checkIfMobile = () => {
  isMobile.value = window.innerWidth < 1024
}

// Lifecycle hooks
onMounted(() => {
  checkIfMobile()
  window.addEventListener('resize', checkIfMobile)
})
</script>

<template>
  <div class="job-filters">
    <!-- Mobile filter dialog -->
    <div class="lg:hidden">
      <div class="fixed inset-0 z-40 flex" v-if="isOpen">
        <div class="fixed inset-0 bg-black bg-opacity-25" @click="togglePanel"></div>
        <div class="relative ml-auto flex h-full w-full max-w-xs flex-col overflow-y-auto bg-white dark:bg-gray-800 py-4 pb-12 shadow-xl">
          <div class="flex items-center justify-between px-4">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Filters</h2>
            <button
              type="button"
              class="-mr-2 flex h-10 w-10 items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
              @click="togglePanel"
            >
              <span class="sr-only">Close menu</span>
              <XMarkIcon class="h-6 w-6" aria-hidden="true" />
            </button>
          </div>

          <!-- Mobile filter form -->
          <form class="mt-4 px-4 space-y-6">
            <!-- Search input -->
            <div>
              <label for="mobile-search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <input
                  type="text"
                  id="mobile-search"
                  v-model="form.q"
                  @keydown.enter="applyFilters"
                  class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  placeholder="Job title, keywords, or company"
                />
              </div>
            </div>

            <!-- Location -->
            <div>
              <label for="mobile-location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
              <div class="mt-1">
                <input
                  type="text"
                  id="mobile-location"
                  v-model="form.location"
                  @keydown.enter="applyFilters"
                  class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  placeholder="City, state, or remote"
                />
              </div>
            </div>

            <!-- Job Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Type</label>
              <div class="mt-1 grid grid-cols-2 gap-2">
                <div v-for="type in jobTypes" :key="type.value" class="flex items-center">
                  <input
                    :id="`mobile-job-type-${type.value}`"
                    v-model="form.job_type"
                    :value="type.value"
                    type="radio"
                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                  />
                  <label :for="`mobile-job-type-${type.value}`" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ type.label }}
                  </label>
                </div>
              </div>
            </div>

            <!-- Remote work -->
            <div class="flex items-center">
              <input
                id="mobile-remote"
                v-model="form.is_remote"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <label for="mobile-remote" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                Remote only
              </label>
            </div>

            <!-- Posted within -->
            <div>
              <label for="mobile-posted-within" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Posted Within</label>
              <select
                id="mobile-posted-within"
                v-model="form.posted_within"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 pl-3 pr-10 text-base focus:border-primary-500 focus:outline-none focus:ring-primary-500 sm:text-sm"
              >
                <option value="">Any time</option>
                <option v-for="option in postedWithinOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>

            <!-- Sort by -->
            <div>
              <label for="mobile-sort-by" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort by</label>
              <select
                id="mobile-sort-by"
                v-model="form.sort_by"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 pl-3 pr-10 text-base focus:border-primary-500 focus:outline-none focus:ring-primary-500 sm:text-sm"
              >
                <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>

            <!-- Action buttons -->
            <div class="flex space-x-3">
              <button
                type="button"
                @click="resetFilters"
                class="flex-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 py-2 px-4 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
              >
                Reset
              </button>
              <button
                type="button"
                @click="applyFilters"
                class="flex-1 rounded-md border border-transparent bg-primary-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
              >
                Apply
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Desktop filters -->
    <div class="hidden lg:block">
      <div class="space-y-6">
        <!-- Search input -->
        <div>
          <label for="desktop-search" class="sr-only">Search</label>
          <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
              </svg>
            </div>
            <input
              type="text"
              id="desktop-search"
              v-model="form.q"
              @keydown.enter="applyFilters"
              class="block w-full rounded-md border-0 bg-white dark:bg-gray-800 py-1.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-600 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-primary-500 sm:text-sm sm:leading-6"
              placeholder="Search jobs..."
            />
          </div>
        </div>

        <!-- Location -->
        <div>
          <label for="desktop-location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
          <div class="mt-1 relative rounded-md shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.682 7.08 7.08 0 00.993.571l.01.005.005.002zM10 11.25a2.25 2.25 0 110-4.5 2.25 2.25 0 010 4.5z" clip-rule="evenodd" />
              </svg>
            </div>
            <input
              type="text"
              id="desktop-location"
              v-model="form.location"
              @keydown.enter="applyFilters"
              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white pl-10 focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
              placeholder="City, state, or remote"
            />
          </div>
        </div>

        <!-- Job Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Type</label>
          <div class="mt-2 space-y-2">
            <div v-for="type in jobTypes" :key="type.value" class="flex items-center">
              <input
                :id="`job-type-${type.value}`"
                v-model="form.job_type"
                :value="type.value"
                type="radio"
                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <label :for="`job-type-${type.value}`" class="ml-3 text-sm text-gray-600 dark:text-gray-300">
                {{ type.label }}
              </label>
            </div>
          </div>
        </div>

        <!-- Experience Level -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Experience Level</label>
          <div class="mt-2 space-y-2">
            <div v-for="level in experienceLevels" :key="level.value" class="flex items-center">
              <input
                :id="`exp-level-${level.value}`"
                v-model="form.experience_level"
                :value="level.value"
                type="radio"
                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <label :for="`exp-level-${level.value}`" class="ml-3 text-sm text-gray-600 dark:text-gray-300">
                {{ level.label }}
              </label>
            </div>
          </div>
        </div>

        <!-- Remote work -->
        <div class="flex items-center">
          <input
            id="remote"
            v-model="form.is_remote"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          />
          <label for="remote" class="ml-2 text-sm text-gray-600 dark:text-gray-300">
            Remote only
          </label>
        </div>

        <!-- Posted within -->
        <div>
          <label for="posted-within" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Posted Within</label>
          <select
            id="posted-within"
            v-model="form.posted_within"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 pl-3 pr-10 text-base focus:border-primary-500 focus:outline-none focus:ring-primary-500 sm:text-sm"
          >
            <option value="">Any time</option>
            <option v-for="option in postedWithinOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Sort by -->
        <div>
          <label for="sort-by" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort by</label>
          <select
            id="sort-by"
            v-model="form.sort_by"
            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-2 pl-3 pr-10 text-base focus:border-primary-500 focus:outline-none focus:ring-primary-500 sm:text-sm"
          >
            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Action buttons -->
        <div class="pt-2">
          <button
            type="button"
            @click="applyFilters"
            class="w-full rounded-md border border-transparent bg-primary-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
          >
            Apply Filters
          </button>
          <button
            type="button"
            @click="resetFilters"
            class="mt-2 w-full text-center text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
          >
            Reset all filters
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile filter button -->
  <div class="lg:hidden">
    <button
      type="button"
      class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600"
      @click="togglePanel"
    >
      <FunnelIcon class="-ml-1 mr-2 h-5 w-5 text-gray-400" aria-hidden="true" />
      <span>Filters</span>
      <span v-if="activeFilterCount > 0" class="ml-1.5 rounded bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 text-xs font-semibold text-gray-700 dark:text-gray-200">
        {{ activeFilterCount }}
      </span>
    </button>
  </div>
</template>

<style scoped>
/* Custom scrollbar for the mobile filter panel */
.job-filters ::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.job-filters ::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.job-filters ::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.dark .job-filters ::-webkit-scrollbar-track {
  background: #374151;
}

.dark .job-filters ::-webkit-scrollbar-thumb {
  background: #6b7280;
}
</style>
