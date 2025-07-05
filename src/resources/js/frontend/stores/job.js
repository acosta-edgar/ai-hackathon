import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useJobStore = defineStore('job', () => {
  // State
  const jobs = ref([])
  const loading = ref(false)
  const error = ref(null)
  const filters = ref({
    search: '',
    location: '',
    type: '',
    experience: '',
    salary: '',
    company: ''
  })
  const pagination = ref({
    currentPage: 1,
    perPage: 10,
    total: 0,
    lastPage: 1
  })

  // Getters
  const filteredJobs = computed(() => {
    let filtered = jobs.value

    if (filters.value.search) {
      const searchTerm = filters.value.search.toLowerCase()
      filtered = filtered.filter(job => 
        job.title.toLowerCase().includes(searchTerm) ||
        job.description.toLowerCase().includes(searchTerm) ||
        job.company_name.toLowerCase().includes(searchTerm)
      )
    }

    if (filters.value.location) {
      filtered = filtered.filter(job => 
        job.location && job.location.toLowerCase().includes(filters.value.location.toLowerCase())
      )
    }

    if (filters.value.type) {
      filtered = filtered.filter(job => job.type === filters.value.type)
    }

    if (filters.value.experience) {
      filtered = filtered.filter(job => job.experience_level === filters.value.experience)
    }

    if (filters.value.company) {
      filtered = filtered.filter(job => 
        job.company_name.toLowerCase().includes(filters.value.company.toLowerCase())
      )
    }

    return filtered
  })

  const paginatedJobs = computed(() => {
    const start = (pagination.value.currentPage - 1) * pagination.value.perPage
    const end = start + pagination.value.perPage
    return filteredJobs.value.slice(start, end)
  })

  const totalJobs = computed(() => filteredJobs.value.length)

  const hasJobs = computed(() => jobs.value.length > 0)

  const isLoading = computed(() => loading.value)

  const hasError = computed(() => error.value !== null)

  // Actions
  const fetchJobs = async (params = {}) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.get('/api/posts', { params })
      jobs.value = response.data.data || response.data
      
      if (response.data.meta) {
        pagination.value = {
          currentPage: response.data.meta.current_page || 1,
          perPage: response.data.meta.per_page || 10,
          total: response.data.meta.total || 0,
          lastPage: response.data.meta.last_page || 1
        }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch jobs'
      console.error('Error fetching jobs:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchJob = async (id) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.get(`/api/posts/${id}`)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch job'
      console.error('Error fetching job:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const searchJobs = async (searchParams) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.get('/api/posts/search', { params: searchParams })
      jobs.value = response.data.data || response.data
      
      if (response.data.meta) {
        pagination.value = {
          currentPage: response.data.meta.current_page || 1,
          perPage: response.data.meta.per_page || 10,
          total: response.data.meta.total || 0,
          lastPage: response.data.meta.last_page || 1
        }
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to search jobs'
      console.error('Error searching jobs:', err)
    } finally {
      loading.value = false
    }
  }

  const scrapeJobDetails = async (url) => {
    try {
      loading.value = true
      error.value = null
      
      const response = await axios.post('/api/posts/scrape', { url })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to scrape job details'
      console.error('Error scraping job details:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  const setFilters = (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
    pagination.value.currentPage = 1 // Reset to first page when filters change
  }

  const clearFilters = () => {
    filters.value = {
      search: '',
      location: '',
      type: '',
      experience: '',
      salary: '',
      company: ''
    }
    pagination.value.currentPage = 1
  }

  const setPage = (page) => {
    pagination.value.currentPage = page
  }

  const setPerPage = (perPage) => {
    pagination.value.perPage = perPage
    pagination.value.currentPage = 1
  }

  const clearError = () => {
    error.value = null
  }

  const clearJobs = () => {
    jobs.value = []
    pagination.value = {
      currentPage: 1,
      perPage: 10,
      total: 0,
      lastPage: 1
    }
  }

  return {
    // State
    jobs,
    loading,
    error,
    filters,
    pagination,
    
    // Getters
    filteredJobs,
    paginatedJobs,
    totalJobs,
    hasJobs,
    isLoading,
    hasError,
    
    // Actions
    fetchJobs,
    fetchJob,
    searchJobs,
    scrapeJobDetails,
    setFilters,
    clearFilters,
    setPage,
    setPerPage,
    clearError,
    clearJobs
  }
}) 