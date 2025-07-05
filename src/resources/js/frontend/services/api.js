import axios from 'axios';

// Create axios instance with base URL and common headers
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: false, // Disabled since we're not using authentication
});

// Request interceptor (no auth required)
api.interceptors.request.use(
  (config) => {
    // Add any common headers here if needed
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for handling common errors
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    // Handle errors (no auth required)
    const errorMessage = error.response?.data?.message || error.message;
    
    // Show error toast if not a 422 validation error
    if (error.response?.status !== 422) {
      console.error('API Error:', errorMessage);
    }
    
    return Promise.reject(error);
  }
);

export default api;
