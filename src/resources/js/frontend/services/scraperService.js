import api from './api';

/**
 * Service for interacting with the job scraper API
 */
const scraperService = {
  /**
   * Search for jobs using the scraper API
   * @param {Object} params - Search parameters
   * @param {string} params.query - Search query
   * @param {string} [params.location] - Location filter
   * @param {string} [params.country='us'] - Country code (default: 'us')
   * @param {number} [params.page=1] - Page number (default: 1)
   * @param {number} [params.limit=10] - Results per page (default: 10)
   * @param {Object} [params.filters] - Additional filters
   * @returns {Promise<{data: Array, meta: Object}>} - Search results and pagination info
   */
  async searchJobs({ query, location, country = 'us', page = 1, limit = 10, filters = {} }) {
    try {
      const response = await api.post('/scraper/search', {
        query,
        location,
        country,
        page,
        limit,
        filters,
      });
      
      return {
        data: response.data.data || [],
        meta: response.data.meta || { total: 0, page, limit }
      };
    } catch (error) {
      console.error('Error searching jobs:', error);
      throw error;
    }
  },

  /**
   * Get job details by ID
   * @param {string} jobId - Job ID
   * @returns {Promise<Object>} - Job details
   */
  async getJobDetails(jobId) {
    try {
      const response = await api.get(`/api/scraper/posts/${jobId}`);
      return response.data.data;
    } catch (error) {
      console.error('Error fetching job details:', error);
      throw error;
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
      const response = await api.get('/scraper/company', {
        params: { company_id, company_url }
      });
      return response.data.data;
    } catch (error) {
      console.error('Error fetching company info:', error);
      throw error;
    }
  },

  /**
   * Get similar jobs for a given job ID
   * @param {string} jobId - Job ID
   * @param {Object} [options] - Options
   * @param {number} [options.limit=5] - Maximum number of similar jobs to return
   * @returns {Promise<Array>} - List of similar jobs
   */
  async getSimilarJobs(jobId, { limit = 5 } = {}) {
    try {
      const response = await api.get(`/api/scraper/posts/${jobId}/similar`, {
        params: { limit }
      });
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching similar jobs:', error);
      return [];
    }
  },

  /**
   * Enrich job data with AI analysis
   * @param {Object} jobData - Job data to enrich
   * @returns {Promise<Object>} - Enriched job data with AI analysis
   */
  async enrichJobWithAi(jobData) {
    try {
      const response = await api.post('/scraper/enrich', {
        job_data: jobData
      });
      return response.data.data || jobData; // Fallback to original data if enrichment fails
    } catch (error) {
      console.error('Error enriching job with AI:', error);
      return jobData; // Return original data if there's an error
    }
  },

  /**
   * Get health status of the scraper service
   * @returns {Promise<Object>} - Health status
   */
  async getHealthStatus() {
    try {
      const response = await api.get('/scraper/health');
      return response.data;
    } catch (error) {
      console.error('Error checking scraper health:', error);
      return { status: 'error', message: 'Service unavailable' };
    }
  }
};

export default scraperService;
