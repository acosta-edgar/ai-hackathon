/**
 * Format a date string to a relative time string (e.g., "2 days ago")
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted relative time string
 */
export const formatRelativeTime = (dateString) => {
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

/**
 * Format a date string to a readable format (e.g., "May 15, 2023")
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted date string
 */
export const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(dateString).toLocaleDateString(undefined, options)
}

/**
 * Get the appropriate badge color based on match score
 * @param {number} score - Match score (0-100)
 * @returns {string} Tailwind CSS color class
 */
export const getMatchBadgeColor = (score) => {
  if (score >= 80) return 'bg-green-100 text-green-800'
  if (score >= 60) return 'bg-blue-100 text-blue-800'
  if (score >= 40) return 'bg-yellow-100 text-yellow-800'
  return 'bg-gray-100 text-gray-800'
}

/**
 * Format salary range for display
 * @param {number} min - Minimum salary
 * @param {number} max - Maximum salary
 * @param {string} currency - Currency code (e.g., 'USD')
 * @param {string} period - Pay period (e.g., 'year', 'month', 'hour')
 * @returns {string} Formatted salary string
 */
export const formatSalary = (min, max, currency = 'USD', period = 'year') => {
  if (!min && !max) return 'Salary not specified'
  
  const formatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  })
  
  const periodText = {
    year: 'year',
    month: 'month',
    hour: 'hour',
    week: 'week'
  }[period] || ''
  
  if (min && max) {
    return `${formatter.format(min)} - ${formatter.format(max)} per ${periodText}`
  }
  
  return min 
    ? `From ${formatter.format(min)} per ${periodText}`
    : `Up to ${formatter.format(max)} per ${periodText}`
}

/**
 * Truncate text to a specified length, adding ellipsis if needed
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Maximum length before truncation
 * @returns {string} Truncated text
 */
export const truncateText = (text, maxLength = 100) => {
  if (!text) return ''
  if (text.length <= maxLength) return text
  return `${text.substring(0, maxLength)}...`
}

/**
 * Parse HTML string to plain text
 * @param {string} html - HTML string
 * @returns {string} Plain text
 */
export const htmlToText = (html) => {
  if (!html) return ''
  
  // Create a temporary div element
  const tempDiv = document.createElement('div')
  tempDiv.innerHTML = html
  
  // Get the text content and normalize whitespace
  return tempDiv.textContent || tempDiv.innerText || ''
    .replace(/\s+/g, ' ') // Replace multiple spaces with a single space
    .trim()
}

/**
 * Format job type for display
 * @param {string} jobType - Job type code
 * @returns {string} Formatted job type
 */
export const formatJobType = (jobType) => {
  const types = {
    'full_time': 'Full-time',
    'part_time': 'Part-time',
    'contract': 'Contract',
    'temporary': 'Temporary',
    'internship': 'Internship',
    'volunteer': 'Volunteer',
    'freelance': 'Freelance',
    'permanent': 'Permanent',
    'apprenticeship': 'Apprenticeship'
  }
  
  return types[jobType] || jobType
}

/**
 * Format experience level for display
 * @param {string} level - Experience level code
 * @returns {string} Formatted experience level
 */
export const formatExperienceLevel = (level) => {
  const levels = {
    'internship': 'Internship',
    'entry_level': 'Entry Level',
    'associate': 'Associate',
    'mid_senior': 'Mid-Senior',
    'senior': 'Senior',
    'lead': 'Lead',
    'manager': 'Manager',
    'executive': 'Executive'
  }
  
  return levels[level] || level
}

export default {
  formatRelativeTime,
  formatDate,
  getMatchBadgeColor,
  formatSalary,
  truncateText,
  htmlToText,
  formatJobType,
  formatExperienceLevel
}
