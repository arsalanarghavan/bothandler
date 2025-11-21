import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import DashboardOverviewView from './DashboardOverviewView.vue';
import { createI18n } from 'vue-i18n';
import { createPinia, setActivePinia } from 'pinia';

// Mock apiClient - define mockGet inside factory to avoid hoisting issues
vi.mock('@/lib/api', () => {
  const mockGet = vi.fn().mockResolvedValue({
    data: {
      data: {
        total: 1,
        running: 1,
        stopped: 0,
        avg_cpu_percent: 0,
        total_mem_usage: 0,
        total_mem_limit: 1,
        total_mem_percent: 0,
      },
    },
  });

  return {
    default: {
      get: mockGet,
    },
    API_BASE: 'https://api.example.com/api',
  };
});

// Mock auth store
vi.mock('@/stores/auth', () => {
  return {
    useAuthStore: () => ({
      token: null,
      isAuthenticated: false,
      user: null,
    }),
  };
});

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  messages: { en: { dashboard: { title: 'Server Overview' } } },
});

describe('DashboardOverviewView', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
  });

  it('renders title', async () => {
    const wrapper = mount(DashboardOverviewView, {
      global: {
        plugins: [i18n, createPinia()],
      },
    });

    // Wait for component to mount
    await wrapper.vm.$nextTick();
    
    // Check if component rendered (basic check)
    const text = wrapper.text();
    expect(text).toMatch(/Dashboard|Loading|Server|Overview/i);
  });
});


