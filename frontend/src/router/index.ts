import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import DashboardOverviewView from '../views/DashboardOverviewView.vue';
import BotsListView from '../views/BotsListView.vue';
import BotDetailView from '../views/BotDetailView.vue';
import ServicesListView from '../views/ServicesListView.vue';
import DeployBotView from '../views/DeployBotView.vue';
import SettingsView from '../views/SettingsView.vue';
import SetupWizardView from '../views/SetupWizardView.vue';

const routes: RouteRecordRaw[] = [
  { path: '/', name: 'dashboard', component: DashboardOverviewView },
  { path: '/bots', name: 'bots', component: BotsListView },
  { path: '/bots/:id', name: 'bot-detail', component: BotDetailView, props: true },
  { path: '/services', name: 'services', component: ServicesListView },
  { path: '/deploy-bot', name: 'deploy-bot', component: DeployBotView },
  { path: '/settings', name: 'settings', component: SettingsView },
  { path: '/setup', name: 'setup', component: SetupWizardView },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;


