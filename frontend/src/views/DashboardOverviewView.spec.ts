import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import DashboardOverviewView from './DashboardOverviewView.vue';
import { createI18n } from 'vue-i18n';

vi.mock('axios', () => {
  return {
    default: {
      get: vi.fn().mockResolvedValue({
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
      }),
    },
  };
});

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  messages: { en: { dashboard: { title: 'Server Overview' } } },
});

describe('DashboardOverviewView', () => {
  it('renders title', () => {
    const wrapper = mount(DashboardOverviewView, {
      global: {
        plugins: [i18n],
      },
    });

    expect(wrapper.text()).toContain('Server Overview');
  });
});


