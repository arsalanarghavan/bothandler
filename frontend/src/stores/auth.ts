import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

const API_BASE = import.meta.env.VITE_API_BASE_URL || 'https://api.example.com/api';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<any>(null);
  const token = ref<string | null>(localStorage.getItem('auth_token'));
  const isAuthenticated = ref<boolean>(!!token.value);
  const isInstalled = ref<boolean | null>(null); // null = not checked yet
  const installCheckPromise = ref<Promise<boolean> | null>(null);

  async function checkInstalled(force = false) {
    // If already checked and not forced, return cached value
    if (isInstalled.value !== null && !force) {
      return isInstalled.value;
    }

    // If already checking, return the same promise
    if (installCheckPromise.value && !force) {
      return installCheckPromise.value;
    }

    // Create new check promise
    installCheckPromise.value = (async () => {
      try {
        const response = await axios.get(`${API_BASE}/setup/status`);
        isInstalled.value = Boolean(response.data?.installed);
        return isInstalled.value;
      } catch (error) {
        // If API fails, assume not installed to show setup wizard
        console.error('Setup status check failed:', error);
        isInstalled.value = false;
        return false;
      } finally {
        installCheckPromise.value = null;
      }
    })();

    return installCheckPromise.value;
  }

  async function login(email: string, password: string) {
    try {
      const response = await axios.post(`${API_BASE}/auth/login`, {
        email,
        password,
      });
      token.value = response.data.token;
      user.value = response.data.user;
      isAuthenticated.value = true;
      localStorage.setItem('auth_token', token.value);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      return true;
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Login failed');
    }
  }

  async function logout() {
    token.value = null;
    user.value = null;
    isAuthenticated.value = false;
    localStorage.removeItem('auth_token');
    delete axios.defaults.headers.common['Authorization'];
  }

  async function checkAuth() {
    if (!token.value) {
      isAuthenticated.value = false;
      return false;
    }
    try {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      const response = await axios.get(`${API_BASE}/auth/me`);
      user.value = response.data.data;
      isAuthenticated.value = true;
      return true;
    } catch {
      await logout();
      return false;
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isInstalled,
    checkInstalled,
    login,
    logout,
    checkAuth,
  };
});

