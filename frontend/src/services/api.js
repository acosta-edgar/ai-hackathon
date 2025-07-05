import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

// Create axios instance with base URL and common headers
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Required for Laravel Sanctum
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore();
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`;
    }
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
    const originalRequest = error.config;
    const authStore = useAuthStore();
    
    // Handle 401 Unauthorized
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      
      try {
        // Attempt to refresh token
        await authStore.refreshToken();
        // Retry the original request with new token
        originalRequest.headers.Authorization = `Bearer ${authStore.token}`;
        return api(originalRequest);
      } catch (refreshError) {
        // If refresh fails, log out user
        await authStore.logout();
        router.push({ name: 'login' });
        return Promise.reject(refreshError);
      }
    }
    
    // Handle other errors
    const errorMessage = error.response?.data?.message || error.message;
    
    // Show error toast if not a 422 validation error
    if (error.response?.status !== 422) {
      // Assuming you have a toast notification system
      // You can replace this with your preferred notification system
      console.error('API Error:', errorMessage);
    }
    
    return Promise.reject(error);
  }
);

export default api;
