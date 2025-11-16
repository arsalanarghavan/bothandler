import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import DashboardOverviewView from './DashboardOverviewView.vue';
import { createI18n } from 'vue-i18n';

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


