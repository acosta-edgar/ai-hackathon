import api from './api';

const aiService = {
  /**
   * Generate match analysis for a job application
   * @param {string|number} jobId - Job ID
   * @param {Object} userProfile - User profile data
   * @returns {Promise<string>} - HTML formatted match analysis
   */
  async generateMatchAnalysis(jobId, userProfile) {
    try {
      const response = await api.post('/ai/match-analysis', {
        job_id: jobId,
        user_profile: userProfile,
      });
      return response.data.analysis;
    } catch (error) {
      console.error('Error generating match analysis:', error);
      throw error;
    }
  },

  /**
   * Generate a cover letter for a job application
   * @param {string|number} jobId - Job ID
   * @param {Object} userProfile - User profile data
   * @param {string} tone - Cover letter tone (e.g., 'professional', 'enthusiastic', 'casual')
   * @param {string} additionalNotes - Additional notes for the cover letter
   * @returns {Promise<string>} - Generated cover letter
   */
  async generateCoverLetter(jobId, userProfile, tone = 'professional', additionalNotes = '') {
    try {
      const response = await api.post('/ai/cover-letter', {
        job_id: jobId,
        user_profile: userProfile,
        tone,
        additional_notes: additionalNotes,
      });
      return response.data.cover_letter;
    } catch (error) {
      console.error('Error generating cover letter:', error);
      throw error;
    }
  },

  /**
   * Optimize resume for a specific job
   * @param {File} resumeFile - Resume file
   * @param {string|number} jobId - Job ID
   * @returns {Promise<{optimized_resume: string, suggestions: Array}>} - Optimized resume and suggestions
   */
  async optimizeResume(resumeFile, jobId) {
    try {
      const formData = new FormData();
      formData.append('resume', resumeFile);
      formData.append('job_id', jobId);

      const response = await api.post('/ai/optimize-resume', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      
      return response.data;
    } catch (error) {
      console.error('Error optimizing resume:', error);
      throw error;
    }
  },

  /**
   * Get interview preparation questions for a job
   * @param {string|number} jobId - Job ID
   * @param {number} count - Number of questions to generate (default: 10)
   * @returns {Promise<Array>} - List of interview questions
   */
  async getInterviewQuestions(jobId, count = 10) {
    try {
      const response = await api.get('/ai/interview-questions', {
        params: {
          job_id: jobId,
          count,
        },
      });
      return response.data.questions;
    } catch (error) {
      console.error('Error fetching interview questions:', error);
      throw error;
    }
  },

  /**
   * Get salary insights for a job title and location
   * @param {string} jobTitle - Job title
   * @param {string} location - Location (city, state, country)
   * @param {string} experienceLevel - Experience level (e.g., 'entry', 'mid', 'senior')
   * @returns {Promise<Object>} - Salary insights
   */
  async getSalaryInsights(jobTitle, location, experienceLevel = null) {
    try {
      const response = await api.get('/ai/salary-insights', {
        params: {
          job_title: jobTitle,
          location,
          experience_level: experienceLevel,
        },
      });
      return response.data;
    } catch (error) {
      console.error('Error fetching salary insights:', error);
      throw error;
    }
  },

  /**
   * Get skills gap analysis
   * @param {Array} userSkills - Array of user's skills
   * @param {string|number} jobId - Job ID
   * @returns {Promise<{missing_skills: Array, learning_resources: Array}>} - Missing skills and learning resources
   */
  async getSkillsGapAnalysis(userSkills, jobId) {
    try {
      const response = await api.post('/ai/skills-gap', {
        user_skills: userSkills,
        job_id: jobId,
      });
      return response.data;
    } catch (error) {
      console.error('Error getting skills gap analysis:', error);
      throw error;
    }
  },

  /**
   * Get company insights
   * @param {string} companyName - Company name
   * @returns {Promise<Object>} - Company information and insights
   */
  async getCompanyInsights(companyName) {
    try {
      const response = await api.get('/ai/company-insights', {
        params: { company_name: companyName },
      });
      return response.data;
    } catch (error) {
      console.error('Error fetching company insights:', error);
      throw error;
    }
  },
};

export default aiService;
