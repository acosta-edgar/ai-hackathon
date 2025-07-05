<script setup>
import { computed } from 'vue'
import { formatDistanceToNow } from 'date-fns'
import { useRouter } from 'vue-router'

const props = defineProps({
  job: {
    type: Object,
    required: true
  },
  isSelected: {
    type: Boolean,
    default: false
  },
  showActions: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['click'])

const router = useRouter()

// Computed properties
const postedDate = computed(() => {
  if (!props.job.posted_at) return ''
  return formatDistanceToNow(new Date(props.job.posted_at), { addSuffix: true })
})

const matchScore = computed(() => {
  if (!props.job.match_score) return null
  return Math.round(props.job.match_score * 100)
})

const handleClick = () => {
  emit('click', props.job)
}

const handleSaveJob = (e) => {
  e.stopPropagation()
  // TODO: Implement save job functionality
  console.log('Save job:', props.job.id)
}

const handleMarkNotInterested = (e) => {
  e.stopPropagation()
  // TODO: Implement not interested functionality
  console.log('Mark not interested:', props.job.id)
}
</script>

<template>
  <div 
    :class="[
      'bg-white dark:bg-gray-800 rounded-lg border shadow-sm overflow-hidden transition-all duration-200',
      isSelected 
        ? 'border-primary-500 ring-2 ring-primary-500' 
        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
    ]"
    @click="handleClick"
  >
    <div class="p-4 sm:p-6">
      <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
          <div class="flex items-center">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white truncate">
              {{ job.title }}
            </h3>
            
            <!-- Match score badge -->
            <span 
              v-if="matchScore"
              :class="[
                'ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                matchScore >= 80 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                matchScore >= 60 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
              ]"
            >
              {{ matchScore }}% Match
            </span>
          </div>
          
          <p class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
            <span class="truncate">{{ job.company_name }}</span>
            <span class="mx-2">•</span>
            <span class="truncate">{{ job.location || 'Remote' }}</span>
            <span class="mx-2">•</span>
            <span class="truncate">{{ job.job_type || 'Full-time' }}</span>
          </p>
          
          <div class="mt-2 flex flex-wrap gap-2">
            <span 
              v-if="job.salary_range"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
            >
              {{ job.salary_range }}
            </span>
            
            <span 
              v-if="job.is_remote"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
            >
              Remote
            </span>
            
            <span 
              v-if="job.requires_relocation"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200"
            >
              Relocation Available
            </span>
          </div>
          
          <div v-if="job.skills && job.skills.length > 0" class="mt-3">
            <div class="flex flex-wrap gap-1.5">
              <span 
                v-for="(skill, index) in job.skills.slice(0, 5)" 
                :key="index"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200"
              >
                {{ skill }}
              </span>
              <span 
                v-if="job.skills.length > 5" 
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-gray-500 dark:text-gray-400"
              >
                +{{ job.skills.length - 5 }} more
              </span>
            </div>
          </div>
        </div>
        
        <div class="ml-4 flex-shrink-0 flex">
          <img 
            v-if="job.company_logo" 
            :src="job.company_logo" 
            :alt="`${job.company_name} logo`"
            class="h-10 w-10 rounded-full object-cover"
          />
          <div 
            v-else
            class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 font-medium"
          >
            {{ job.company_name ? job.company_name.charAt(0).toUpperCase() : '?' }}
          </div>
        </div>
      </div>
      
      <!-- Job description preview -->
      <div class="mt-3 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
        {{ job.description_preview || job.description?.substring(0, 200) + '...' }}
      </div>
      
      <!-- Footer -->
      <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
          <span>{{ postedDate }}</span>
          <span v-if="job.source" class="ml-2">• {{ job.source }}</span>
        </div>
        
        <div v-if="showActions" class="flex space-x-2">
          <button
            type="button"
            @click.stop="handleMarkNotInterested"
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Not Interested
          </button>
          <button
            type="button"
            @click.stop="handleSaveJob"
            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            Save Job
          </button>
        </div>
      </div>
    </div>
    
    <!-- Selected indicator -->
    <div 
      v-if="isSelected" 
      class="h-1 bg-primary-500"
    ></div>
  </div>
</template>

<style scoped>
/* Line clamp for description preview */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
