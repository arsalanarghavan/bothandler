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
  { path: '/login', name: 'login', component: LoginView, meta: { requiresAuth: false } },
  { path: '/setup', name: 'setup', component: SetupWizardView, meta: { requiresAuth: false, isSetup: true } },
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
  
  // Skip checks for setup and login routes on first navigation
  // (App.vue handles initial check)
  if (!from.name && (to.path === '/setup' || to.path === '/login')) {
    return next();
  }
  
  // Check installation status (cached in auth store)
  const isInstalled = await authStore.checkInstalled();
  
  // If not installed, only allow /setup
  if (!isInstalled) {
    if (to.path !== '/setup') {
      return next('/setup');
    }
    return next();
  }
  
  // If going to setup but already installed, redirect to login
  if (to.path === '/setup' && isInstalled) {
    return next('/login');
  }
  
  // If going to login and already authenticated, redirect to home
  if (to.path === '/login' && authStore.isAuthenticated) {
    return next('/');
  }
  
  // For protected routes, check authentication
  if (to.meta.requiresAuth) {
    const isAuth = await authStore.checkAuth();
    if (!isAuth) {
      return next('/login');
    }
  }
  
  // Allow navigation
  next();
});

export default router;
