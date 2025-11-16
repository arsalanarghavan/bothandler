import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import DashboardOverviewView from '../views/DashboardOverviewView.vue';
import BotsListView from '../views/BotsListView.vue';
import BotDetailView from '../views/BotDetailView.vue';
import ServicesListView from '../views/ServicesListView.vue';
import DeployBotView from '../views/DeployBotView.vue';
import SettingsView from '../views/SettingsView.vue';
import SetupWizardView from '../views/SetupWizardView.vue';
import LoginView from '../views/LoginView.vue';
import { useAuthStore } from '../stores/auth';

const routes: RouteRecordRaw[] = [
  { path: '/login', name: 'login', component: LoginView, meta: { public: true } },
  { path: '/setup', name: 'setup', component: SetupWizardView, meta: { public: true } },
  { path: '/', name: 'dashboard', component: DashboardOverviewView, meta: { requiresAuth: true } },
  { path: '/bots', name: 'bots', component: BotsListView, meta: { requiresAuth: true } },
  { path: '/bots/:id', name: 'bot-detail', component: BotDetailView, props: true, meta: { requiresAuth: true } },
  { path: '/services', name: 'services', component: ServicesListView, meta: { requiresAuth: true } },
  { path: '/deploy-bot', name: 'deploy-bot', component: DeployBotView, meta: { requiresAuth: true } },
  { path: '/settings', name: 'settings', component: SettingsView, meta: { requiresAuth: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  // Check if app is installed
  const installed = await authStore.checkInstalled();
  
  if (!installed && to.path !== '/setup') {
    return next('/setup');
  }
  
  if (installed && to.path === '/setup') {
    return next('/login');
  }
  
  // Check authentication for protected routes
  if (to.meta.requiresAuth) {
    const isAuth = await authStore.checkAuth();
    if (!isAuth) {
      return next('/login');
    }
  }
  
  // Redirect authenticated users away from login/setup
  if ((to.path === '/login' || to.path === '/setup') && authStore.isAuthenticated) {
    return next('/');
  }
  
  next();
});

export default router;


