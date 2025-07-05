import api from './api';
import scraperService from './scraperService';

/**
 * Service for interacting with job-related API endpoints
 */
const jobService = {
  /**
   * Search for jobs with filters and pagination
   * @param {Object} params - Search parameters
   * @param {string} params.query - Search query
   * @param {string} [params.location] - Location filter
   * @param {string} [params.country='us'] - Country code (default: 'us')
   * @param {number} [page=1] - Page number (default: 1)
   * @param {number} [perPage=10] - Items per page (default: 10)
   * @param {Object} [filters] - Additional filters
   * @returns {Promise<{data: Array, meta: Object}>} - Jobs data and pagination info
   */
  async searchJobs({ query, location, country = 'us' } = {}, page = 1, perPage = 10, filters = {}) {
    try {
      // First try the scraper service for external job search
      const scraperResult = await scraperService.searchJobs({
        query,
        location,
        country,
        page,
        limit: perPage,
        filters,
      });

      // If we get results from the scraper, return them
      if (scraperResult.data && scraperResult.data.length > 0) {
        return {
          data: scraperResult.data,
          meta: {
            current_page: page,
            per_page: perPage,
            total: scraperResult.meta?.total || scraperResult.data.length,
            last_page: Math.ceil((scraperResult.meta?.total || scraperResult.data.length) / perPage),
          },
          source: 'scraper',
        };
      }

      // Fallback to internal job search if no results from scraper
      const params = {
        query,
        location,
        country,
        page,
        per_page: perPage,
        ...filters,
      };

      const response = await api.get('/api/posts/search', { params });
      return {
        ...response.data,
        source: 'database',
      };
    } catch (error) {
      console.error('Error searching jobs:', error);
      throw error;
    }
  },

  /**
   * Get job details by ID
   * @param {string|number} jobId - Job ID
   * @param {Object} [options] - Additional options
   * @param {boolean} [options.enrichWithAi=false] - Whether to enrich job with AI analysis
   * @returns {Promise<Object>} - Job details
   */
  async getJobDetails(jobId, { enrichWithAi = false } = {}) {
    try {
      // First try to get from our database
      try {
        const response = await api.get(`/api/posts/${jobId}`);
        const jobData = response.data.data;
        
        // If AI enrichment is requested and we have a description
        if (enrichWithAi && jobData.description) {
          try {
            const enrichedJob = await scraperService.enrichJobWithAi(jobData);
            return enrichedJob;
          } catch (aiError) {
            console.warn('AI enrichment failed, returning basic job data', aiError);
            return jobData;
          }
        }
        
        return jobData;
      } catch (dbError) {
        // If not found in database, try the scraper service
        if (dbError.response?.status === 404) {
          console.log('Job not found in database, trying scraper service...');
          return scraperService.getJobDetails(jobId);
        }
        throw dbError;
      }
    } catch (error) {
      console.error('Error fetching job details:', error);
      throw error;
    }
  },

  /**
   * Get similar jobs for a given job ID
   * @param {string|number} jobId - Job ID
   * @param {Object} [options] - Additional options
   * @param {number} [options.limit=5] - Maximum number of similar jobs to return
   * @returns {Promise<Array>} - List of similar jobs
   */
  async getSimilarJobs(jobId, { limit = 5 } = {}) {
    try {
      // First try the scraper service for similar jobs
      const similarJobs = await scraperService.getSimilarJobs(jobId, { limit });
      
      // If we have enough similar jobs, return them
      if (similarJobs && similarJobs.length >= limit) {
        return similarJobs;
      }
      
      // Otherwise, fall back to internal similar jobs endpoint
      const response = await api.get(`/api/posts/${jobId}/similar`, {
        params: { limit },
      });
      
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching similar jobs:', error);
      return [];
    }
  },

  /**
   * Save or unsave a job
   * @param {string|number} jobId - Job ID
   * @param {boolean} [save=true] - Whether to save (true) or unsave (false)
   * @returns {Promise<boolean>} - New save status
   */
  async toggleSaveJob(jobId, save = true) {
    try {
      const response = save
              ? await api.post(`/api/posts/${jobId}/save`)
      : await api.delete(`/api/posts/${jobId}/save`);
      
      return response.data.data.saved;
    } catch (error) {
      console.error('Error toggling save job:', error);
      throw error;
    }
  },

  /**
   * Apply for a job
   * @param {string|number} jobId - Job ID
   * @param {Object} applicationData - Application data
   * @param {File} [applicationData.resume] - Resume file
   * @param {string} [applicationData.coverLetter] - Cover letter text
   * @param {Object} [applicationData.answers] - Answers to application questions
   * @returns {Promise<Object>} - Application result
   */
  async applyForJob(jobId, applicationData = {}) {
    try {
      const formData = new FormData();
      
      // Append all fields to form data
      Object.entries(applicationData).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          // Handle file uploads
          if (value instanceof File || value instanceof Blob) {
            formData.append(key, value);
          } else if (typeof value === 'object') {
            formData.append(key, JSON.stringify(value));
          } else {
            formData.append(key, value);
          }
        }
      });
      
      const response = await api.post(`/api/posts/${jobId}/apply`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      
      return response.data.data;
    } catch (error) {
      console.error('Error applying for job:', error);
      throw error;
    }
  },

  /**
   * Get application status for a job
   * @param {string|number} jobId - Job ID
   * @returns {Promise<Object>} - Application status
   */
  async getApplicationStatus(jobId) {
    try {
      const response = await api.get(`/api/posts/${jobId}/application-status`);
      return response.data.data;
    } catch (error) {
      // If not found, return default status
      if (error.response?.status === 404) {
        return {
          status: 'not_applied',
          last_updated: new Date().toISOString(),
        };
      }
      console.error('Error fetching application status:', error);
      throw error;
    }
  },

  /**
   * Get saved jobs
   * @param {number} [page=1] - Page number
   * @param {number} [perPage=10] - Items per page
   * @returns {Promise<{data: Array, meta: Object}>} - Saved jobs and pagination info
   */
  async getSavedJobs(page = 1, perPage = 10) {
    try {
      const response = await api.get('/api/posts/saved', {
        params: { page, per_page: perPage },
      });
      
      return {
        data: response.data.data,
        meta: response.data.meta,
      };
    } catch (error) {
      console.error('Error fetching saved jobs:', error);
      return { data: [], meta: { total: 0, current_page: page, per_page: perPage, last_page: 1 } };
    }
  },

  /**
   * Get application history
   * @param {number} [page=1] - Page number
   * @param {number} [perPage=10] - Items per page
   * @returns {Promise<{data: Array, meta: Object}>} - Application history and pagination info
   */
  async getApplicationHistory(page = 1, perPage = 10) {
    try {
      const response = await api.get('/applications', {
        params: { page, per_page: perPage },
      });
      
      return {
        data: response.data.data,
        meta: response.data.meta,
      };
    } catch (error) {
      console.error('Error fetching application history:', error);
      return { data: [], meta: { total: 0, current_page: page, per_page: perPage, last_page: 1 } };
    }
  },

  /**
   * Get company information
   * @param {Object} params - Company lookup parameters
   * @param {string} [params.company_id] - Company ID
   * @param {string} [params.company_url] - Company URL
   * @returns {Promise<Object>} - Company information
   */
  async getCompanyInfo({ company_id, company_url }) {
    try {
      return await scraperService.getCompanyInfo({ company_id, company_url });
    } catch (error) {
      console.error('Error fetching company info:', error);
      return null;
    }
  },
};

export default jobService;
