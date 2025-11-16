<template>
  <div class="page">
    <AppHeader />
    <AppSidebar />
    <div class="main-content app-content">
      <div class="side-app">
        <div class="main-container container-fluid">
          <slot />
        </div>
      </div>
    </div>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
import AppHeader from '../components/layout/AppHeader.vue';
import AppSidebar from '../components/layout/AppSidebar.vue';
import AppFooter from '../components/layout/AppFooter.vue';
</script>


