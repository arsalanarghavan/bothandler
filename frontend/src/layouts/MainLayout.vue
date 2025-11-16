<template>
  <div class="page">
    <AppHeader />
    <div class="page-content">
      <AppSidebar />
      <main class="main-content">
        <slot />
      </main>
    </div>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
import AppHeader from '../components/layout/AppHeader.vue';
import AppSidebar from '../components/layout/AppSidebar.vue';
import AppFooter from '../components/layout/AppFooter.vue';
</script>


