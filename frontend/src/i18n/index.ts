import { createI18n } from 'vue-i18n';
import fa from './locales/fa.json';
import en from './locales/en.json';

const savedLocale = localStorage.getItem('locale') || 'fa';

const i18n = createI18n({
  legacy: false,
  locale: savedLocale,
  fallbackLocale: 'en',
  messages: {
    fa,
    en,
  },
});

export default i18n;


