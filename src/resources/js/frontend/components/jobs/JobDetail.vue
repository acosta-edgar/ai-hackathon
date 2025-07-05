<template>
  <div class="job-detail" v-if="job">
    <!-- Header Section -->
    <div class="job-header">
      <div class="job-title-section">
        <h1 class="text-2xl font-bold text-gray-900">{{ job.title }}</h1>
        <div class="company-info flex items-center mt-2">
          <img v-if="job.company_logo" :src="job.company_logo" :alt="job.company_name + ' logo'" class="h-12 w-12 object-contain mr-3">
          <div>
            <h2 class="text-lg font-semibold text-gray-800">{{ job.company_name }}</h2>
            <div class="text-sm text-gray-600">
              <span v-if="job.location">{{ job.location }}</span>
              <span v-if="job.job_type" class="mx-1">•</span>
              <span v-if="job.job_type">{{ job.job_type }}</span>
              <span v-if="job.salary" class="mx-1">•</span>
              <span v-if="job.salary">{{ job.salary }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="job-actions">
        <button 
          @click="saveJob" 
          class="btn btn-primary mr-2"
          :class="{ 'btn-outline': !isSaved }"
        >
          {{ isSaved ? 'Saved' : 'Save Job' }}
        </button>
        <button 
          @click="applyForJob" 
          class="btn btn-primary"
          :disabled="isApplied" 
          :class="{ 'btn-disabled': isApplied }"
        >
          {{ isApplied ? 'Applied' : 'Apply Now' }}
        </button>
      </div>
    </div>

    <!-- Main Content -->
    <div class="job-content">
      <!-- Left Column -->
      <div class="job-description">
        <!-- Job Metadata -->
        <div class="job-meta bg-gray-50 p-4 rounded-lg mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="meta-item">
              <span class="text-sm text-gray-500">Posted</span>
              <p class="font-medium">{{ formatDate(job.posted_at) }}</p>
            </div>
            <div class="meta-item" v-if="job.experience_level">
              <span class="text-sm text-gray-500">Experience</span>
              <p class="font-medium">{{ job.experience_level }}</p>
            </div>
            <div class="meta-item" v-if="job.job_type">
              <span class="text-sm text-gray-500">Job Type</span>
              <p class="font-medium">{{ job.job_type }}</p>
            </div>
          </div>
        </div>

        <!-- Job Description -->
        <div class="job-section">
          <h3 class="text-lg font-semibold mb-3">Job Description</h3>
          <div class="prose max-w-none" v-html="job.description"></div>
        </div>

        <!-- Requirements -->
        <div class="job-section mt-8" v-if="job.requirements">
          <h3 class="text-lg font-semibold mb-3">Requirements</h3>
          <ul class="list-disc pl-5 space-y-2">
            <li v-for="(requirement, index) in job.requirements" :key="index">
              {{ requirement }}
            </li>
          </ul>
        </div>

        <!-- Responsibilities -->
        <div class="job-section mt-8" v-if="job.responsibilities">
          <h3 class="text-lg font-semibold mb-3">Responsibilities</h3>
          <ul class="list-disc pl-5 space-y-2">
            <li v-for="(responsibility, index) in job.responsibilities" :key="index">
              {{ responsibility }}
            </li>
          </ul>
        </div>

        <!-- Benefits -->
        <div class="job-section mt-8" v-if="job.benefits">
          <h3 class="text-lg font-semibold mb-3">Benefits</h3>
          <ul class="list-disc pl-5 space-y-2">
            <li v-for="(benefit, index) in job.benefits" :key="index">
              {{ benefit }}
            </li>
          </ul>
        </div>

        <!-- AI Match Analysis -->
        <div class="job-section mt-8" v-if="matchAnalysis">
          <h3 class="text-lg font-semibold mb-3">Match Analysis</h3>
          <div class="bg-blue-50 p-4 rounded-lg">
            <div v-html="matchAnalysis"></div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="job-sidebar">
        <!-- Company Info -->
        <div class="sidebar-section">
          <h3 class="text-lg font-semibold mb-3">About the Company</h3>
          <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="flex items-center mb-3">
              <img v-if="job.company_logo" :src="job.company_logo" :alt="job.company_name + ' logo'" class="h-12 w-12 object-contain mr-3">
              <h4 class="text-lg font-semibold">{{ job.company_name }}</h4>
            </div>
            <p class="text-gray-600 text-sm mb-3" v-if="job.company_description">
              {{ job.company_description }}
            </p>
            <div class="space-y-2 text-sm">
              <div v-if="job.company_website">
                <span class="text-gray-500">Website:</span>
                <a :href="job.company_website" target="_blank" class="text-blue-600 hover:underline ml-1">
                  {{ job.company_website.replace('https://', '') }}
                </a>
              </div>
              <div v-if="job.company_size">
                <span class="text-gray-500">Company Size:</span>
                <span class="ml-1">{{ job.company_size }} employees</span>
              </div>
              <div v-if="job.company_industry">
                <span class="text-gray-500">Industry:</span>
                <span class="ml-1">{{ job.company_industry }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Similar Jobs -->
        <div class="sidebar-section mt-6" v-if="similarJobs && similarJobs.length > 0">
          <h3 class="text-lg font-semibold mb-3">Similar Jobs</h3>
          <div class="space-y-4">
            <div v-for="similarJob in similarJobs" :key="similarJob.id" class="bg-white p-4 rounded-lg border border-gray-200 hover:border-blue-300 cursor-pointer" @click="$router.push(`/jobs/${similarJob.id}`)">
              <h4 class="font-semibold text-blue-600">{{ similarJob.title }}</h4>
              <p class="text-sm text-gray-600">{{ similarJob.company_name }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ similarJob.location }}</p>
              <div class="mt-2 flex justify-between items-center">
                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">{{ similarJob.match_score }}% Match</span>
                <span class="text-xs text-gray-500">{{ formatRelativeTime(similarJob.posted_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading State -->
  <div v-else-if="loading" class="text-center py-12">
    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
    <p class="mt-4 text-gray-600">Loading job details...</p>
  </div>

  <!-- Error State -->
  <div v-else class="text-center py-12">
    <div class="text-red-500 mb-4">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
    </div>
    <h3 class="text-lg font-medium text-gray-900 mb-2">Job not found</h3>
    <p class="text-gray-600 mb-6">We couldn't find the job you're looking for.</p>
    <button @click="$router.push('/jobs')" class="btn btn-primary">
      Browse All Jobs
    </button>
  </div>
</template>

<script>
export default {
  name: 'JobDetail',
  props: {
    jobId: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      job: null,
      similarJobs: [],
      matchAnalysis: null,
      loading: false,
      error: null,
      isSaved: false,
      isApplied: false,
      loadingAnalysis: false,
      generatingCoverLetter: false,
      coverLetter: '',
      coverLetterError: null,
      copyButtonText: 'Copy to clipboard'
    }
  },
  created() {
    this.fetchJobDetails()
  },
  watch: {
    jobId() {
      this.fetchJobDetails()
    }
  },
  methods: {
    async fetchJobDetails() {
      this.loading = true
      this.error = null
      
      try {
        // Fetch job details
        const response = await this.$axios.get(`/api/posts/${this.jobId}`)
        this.job = response.data.data
        
        // Fetch similar jobs
        await this.fetchSimilarJobs()
        
        // Check if job is saved/applied
        await this.checkJobStatus()
        
        // Load match analysis if available
        if (this.job.match_analysis) {
          this.matchAnalysis = this.job.match_analysis
        }
      } catch (error) {
        console.error('Error fetching job details:', error)
        this.error = 'Failed to load job details. Please try again later.'
      } finally {
        this.loading = false
      }
    },
    
    async generateMatchAnalysis() {
      if (!this.job) return
      
      this.loadingAnalysis = true
      this.matchAnalysis = null
      this.error = null
      
      try {
        const response = await this.$axios.post(`/api/posts/${this.jobId}/analyze-match`)
        this.matchAnalysis = response.data.analysis
        
        // Update the job with the new analysis
        if (this.job) {
          this.job.match_analysis = this.matchAnalysis
        }
        
        this.$toast.success('Match analysis generated successfully!')
      } catch (error) {
        console.error('Error generating match analysis:', error)
        this.error = 'Failed to generate match analysis. Please try again.'
        this.$toast.error('Failed to generate match analysis')
      } finally {
        this.loadingAnalysis = false
      }
    },
    
    async generateCoverLetter() {
      if (!this.job) return
      
      this.generatingCoverLetter = true
      this.coverLetter = ''
      this.coverLetterError = null
      
      try {
        const response = await this.$axios.post(`/api/posts/${this.jobId}/generate-cover-letter`, {
          tone: 'professional',
          highlight_skills: true,
          include_salary_expectations: false
        })
        
        this.coverLetter = response.data.cover_letter
        this.$toast.success('Cover letter generated successfully!')
      } catch (error) {
        console.error('Error generating cover letter:', error)
        this.coverLetterError = error.response?.data?.message || 'Failed to generate cover letter. Please try again.'
        this.$toast.error('Failed to generate cover letter')
      } finally {
        this.generatingCoverLetter = false
      }
    },
    
    async copyToClipboard(text) {
      try {
        await navigator.clipboard.writeText(text)
        this.copyButtonText = 'Copied!'
        this.$toast.success('Copied to clipboard')
        
        // Reset button text after 2 seconds
        setTimeout(() => {
          this.copyButtonText = 'Copy to clipboard'
        }, 2000)
      } catch (err) {
        console.error('Failed to copy text:', err)
        this.$toast.error('Failed to copy to clipboard')
      }
    },
    
    formatCoverLetter(text) {
      // Convert line breaks to <p> tags
      return text.replace(/\n\n+/g, '</p><p>').replace(/\n/g, '<br>')
    },
    
    getMatchScoreColor(score) {
      if (score >= 80) return '#10B981' // green-500
      if (score >= 60) return '#3B82F6' // blue-500
      if (score >= 40) return '#F59E0B' // yellow-500
      return '#EF4444' // red-500
    },
    
    getMatchScoreTextColor(score) {
      if (score >= 80) return 'text-green-600'
      if (score >= 60) return 'text-blue-600'
      if (score >= 40) return 'text-yellow-600'
      return 'text-red-600'
    },
    
    getMatchScoreClass(score) {
      if (score >= 80) return 'bg-green-500'
      if (score >= 60) return 'bg-blue-500'
      if (score >= 40) return 'bg-yellow-500'
      return 'bg-red-500'
    },
    
    async fetchSimilarJobs() {
      try {
        const response = await this.$axios.get('/api/posts', {
          params: {
            company: this.job.company_id,
            exclude: this.job.id,
            limit: 3
          }
        })
        this.similarJobs = response.data.data
      } catch (error) {
        console.error('Error fetching similar jobs:', error)
      }
    },
    
    async checkJobStatus() {
      try {
        // Check if job is saved
        const savedResponse = await this.$axios.get(`/api/user/saved-jobs/${this.jobId}`)
        this.isSaved = savedResponse.data.is_saved
        
        // Check if job is already applied
        const appliedResponse = await this.$axios.get(`/api/user/applications/${this.jobId}/status`)
        this.isApplied = appliedResponse.data.is_applied
      } catch (error) {
        console.error('Error checking job status:', error)
      }
    },
    
    async loadMatchAnalysis() {
      if (this.job.match_analysis) {
        this.matchAnalysis = this.job.match_analysis
        return
      }
      
      this.loadingAnalysis = true
      try {
        const response = await this.$axios.get(`/api/posts/${this.jobId}/match-analysis`)
        this.matchAnalysis = response.data.analysis
      } catch (error) {
        console.error('Error loading match analysis:', error)
      } finally {
        this.loadingAnalysis = false
      }
    },
    
    async saveJob() {
      try {
        if (this.isSaved) {
          await this.$axios.delete(`/api/user/saved-jobs/${this.jobId}`)
        } else {
          await this.$axios.post('/api/user/saved-jobs', { job_id: this.jobId })
        }
        this.isSaved = !this.isSaved
      } catch (error) {
        console.error('Error updating saved status:', error)
        this.$toast.error('Failed to update saved status')
      }
    },
    
    async applyForJob() {
      if (this.isApplied) return
      
      try {
        await this.$axios.post('/api/user/applications', { job_id: this.jobId })
        this.isApplied = true
        this.$toast.success('Application submitted successfully!')
      } catch (error) {
        console.error('Error applying for job:', error)
        this.$toast.error('Failed to submit application')
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      return new Date(dateString).toLocaleDateString(undefined, options)
    },
    
    formatRelativeTime(dateString) {
      if (!dateString) return ''
      
      const date = new Date(dateString)
      const now = new Date()
      const seconds = Math.floor((now - date) / 1000)
      
      let interval = Math.floor(seconds / 31536000)
      if (interval >= 1) return `${interval} year${interval === 1 ? '' : 's'} ago`
      
      interval = Math.floor(seconds / 2592000)
      if (interval >= 1) return `${interval} month${interval === 1 ? '' : 's'} ago`
      
      interval = Math.floor(seconds / 86400)
      if (interval >= 1) return `${interval} day${interval === 1 ? '' : 's'} ago`
      
      interval = Math.floor(seconds / 3600)
      if (interval >= 1) return `${interval} hour${interval === 1 ? '' : 's'} ago`
      
      interval = Math.floor(seconds / 60)
      if (interval >= 1) return `${interval} minute${interval === 1 ? '' : 's'} ago`
      
      return 'Just now'
    }
  }
}
</script>

<style scoped>
.job-detail {
  @apply max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8;
}

.job-header {
  @apply flex flex-col md:flex-row justify-between items-start md:items-center mb-8 pb-6 border-b border-gray-200;
}

.company-info {
  @apply flex items-center mt-4 md:mt-0;
}

.job-actions {
  @apply mt-4 md:mt-0 flex space-x-2;
}

.job-content {
  @apply flex flex-col lg:flex-row gap-8;
}

.job-description {
  @apply flex-1;
}

.job-sidebar {
  @apply w-full lg:w-80 flex-shrink-0;
}

.job-section {
  @apply mb-8;
}

.sidebar-section {
  @apply mb-6;
}

.meta-item {
  @apply p-3 bg-white rounded-lg border border-gray-200;
}

.btn {
  @apply px-4 py-2 rounded-md font-medium transition-colors duration-200;
}

.btn-primary {
  @apply bg-blue-600 text-white hover:bg-blue-700;
}

.btn-outline {
  @apply bg-white text-blue-600 border border-blue-600 hover:bg-blue-50;
}

.btn-disabled {
  @apply bg-green-100 text-green-800 cursor-not-allowed;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .job-content {
    @apply flex-col;
  }
  
  .job-sidebar {
    @apply w-full mt-8;
  }
}
</style>
