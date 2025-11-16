<template>
  <MainLayout v-if="showLayout">
    <RouterView />
  </MainLayout>
  <RouterView v-else />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { RouterView } from 'vue-router';
import MainLayout from './layouts/MainLayout.vue';

const route = useRoute();
const showLayout = computed(() => {
  return route.path !== '/login' && route.path !== '/setup';
});
</script>


