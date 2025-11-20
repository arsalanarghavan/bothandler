<template>
  <LoadingScreen v-if="isInitializing" />
  <MainLayout v-else-if="showLayout">
    <RouterView />
  </MainLayout>
  <RouterView v-else />
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { RouterView } from 'vue-router';
import MainLayout from './layouts/MainLayout.vue';
import LoadingScreen from './components/LoadingScreen.vue';
import { useAuthStore } from './stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const isInitializing = ref(true);

const showLayout = computed(() => {
  return route.path !== '/login' && route.path !== '/setup';
});

onMounted(async () => {
  // Check installation status on app mount
  const installed = await authStore.checkInstalled();
  
  // Navigate to appropriate route
  if (!installed && route.path !== '/setup') {
    router.push('/setup');
  } else if (installed && route.path === '/setup') {
    router.push('/login');
  }
  
  // Hide loading screen
  isInitializing.value = false;
});
</script>


