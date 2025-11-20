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
  { path: '/login', name: 'login', component: LoginView },
  { path: '/setup', name: 'setup', component: SetupWizardView },
  { path: '/', name: 'dashboard', component: DashboardOverviewView },
  { path: '/bots', name: 'bots', component: BotsListView },
  { path: '/bots/:id', name: 'bot-detail', component: BotDetailView, props: true },
  { path: '/services', name: 'services', component: ServicesListView },
  { path: '/deploy-bot', name: 'deploy-bot', component: DeployBotView },
  { path: '/settings', name: 'settings', component: SettingsView },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  // Public routes that don't need any checks
  const publicRoutes = ['/login', '/setup'];
  const isPublicRoute = publicRoutes.includes(to.path);
  
  // If going to public route and already authenticated, redirect to home
  if (isPublicRoute && authStore.isAuthenticated) {
    return next('/');
  }
  
  // If going to setup and already installed, redirect to login
  if (to.path === '/setup') {
    const installed = await authStore.checkInstalled();
    if (installed) {
      return next('/login');
    }
    return next();
  }
  
  // For all non-public routes, check installation first
  if (!isPublicRoute) {
    const installed = await authStore.checkInstalled();
    if (!installed) {
      return next('/setup');
    }
    
    // Then check authentication
    const isAuth = await authStore.checkAuth();
    if (!isAuth) {
      return next('/login');
    }
  }
  
  // Allow navigation
  next();
});

export default router;
