import axios, { AxiosError, AxiosInstance, InternalAxiosRequestConfig } from 'axios';
import { useAuthStore } from '@/stores/auth';

// API base URL from environment or default
export const API_BASE = import.meta.env.VITE_API_BASE_URL || 'https://api.example.com/api';

// Create axios instance
const apiClient: AxiosInstance = axios.create({
  baseURL: API_BASE,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor: Attach auth token
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
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

// Response interceptor: Handle errors globally
apiClient.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error: AxiosError) => {
    const authStore = useAuthStore();
    
    // Handle 401 Unauthorized - auto logout
    if (error.response?.status === 401) {
      // Don't logout on login endpoint
      if (!error.config?.url?.includes('/auth/login')) {
        await authStore.logout();
        // Redirect to login if not already there
        if (window.location.pathname !== '/login') {
          window.location.href = '/login';
        }
      }
    }
    
    // Handle 403 Forbidden
    if (error.response?.status === 403) {
      // Could show a toast notification here
      console.error('Access forbidden');
    }
    
    // Format error message for consistent handling
    const errorMessage = error.response?.data?.message 
      || error.response?.data?.error 
      || error.message 
      || 'An error occurred';
    
    // Create a formatted error object
    const formattedError = {
      message: errorMessage,
      status: error.response?.status,
      data: error.response?.data,
      originalError: error,
    };
    
    return Promise.reject(formattedError);
  }
);

export default apiClient;

