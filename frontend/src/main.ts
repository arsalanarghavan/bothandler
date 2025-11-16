import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import i18n from './i18n';

import './assets/styles.css';
import axios from 'axios';

const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api';

async function bootstrap() {
  const app = createApp(App);

  app.use(createPinia());
  app.use(router);
  app.use(i18n);

  const installed = await checkInstalled();

  if (!installed && window.location.pathname !== '/setup') {
    router.replace('/setup');
  }

  app.mount('#app');
}

async function checkInstalled(): Promise<boolean> {
  try {
    const response = await axios.get(`${API_BASE}/setup/status`);
    return Boolean(response.data?.installed);
  } catch {
    return true;
  }
}

bootstrap();


