<template>
  <aside class="app-sidebar sticky" id="sidebar">
    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
      <RouterLink to="/" class="header-logo">
        <img src="/assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
        <img src="/assets/images/brand-logos/toggle-dark.png" alt="logo" class="toggle-dark">
        <img src="/assets/images/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
        <img src="/assets/images/brand-logos/toggle-logo.png" alt="logo" class="toggle-logo">
        <img src="/assets/images/brand-logos/toggle-white.png" alt="logo" class="toggle-white">
        <img src="/assets/images/brand-logos/desktop-white.png" alt="logo" class="desktop-white">
      </RouterLink>
    </div>
    <!-- End::main-sidebar-header -->

    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">
      <!-- Start::nav -->
      <nav class="main-menu-container nav nav-pills flex-column sub-open">
        <div class="slide-left" id="slide-left">
          <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
            <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
          </svg>
        </div>
        <ul class="main-menu">
          <!-- Dashboard Category -->
          <li class="slide__category"><span class="category-name">{{ $t('nav.main') }}</span></li>

          <!-- Dashboard -->
          <li class="slide" :class="{ 'active': $route.path === '/' }">
            <RouterLink to="/" class="side-menu__item">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 side-menu__icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"></path>
              </svg>
              <span class="side-menu__label">{{ $t('nav.dashboard') }}</span>
            </RouterLink>
          </li>

          <!-- Services Category -->
          <li class="slide__category"><span class="category-name">{{ $t('nav.services') }}</span></li>

          <!-- Services List -->
          <li class="slide" :class="{ 'active': $route.path === '/bots' }">
            <RouterLink to="/bots" class="side-menu__item">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 side-menu__icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-16.5 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z"></path>
              </svg>
              <span class="side-menu__label">{{ $t('nav.services') }}</span>
            </RouterLink>
          </li>

          <!-- Deploy Service -->
          <li class="slide" :class="{ 'active': $route.path === '/deploy-bot' }">
            <RouterLink to="/deploy-bot" class="side-menu__item">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 side-menu__icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
              </svg>
              <span class="side-menu__label">{{ $t('nav.deployService') }}</span>
            </RouterLink>
          </li>

          <!-- Services Monitoring -->
          <li class="slide" :class="{ 'active': $route.path === '/services' }">
            <RouterLink to="/services" class="side-menu__item">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 side-menu__icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Zm9.75-3C12.75 8.504 13.254 8 13.875 8h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V10.125Zm9.75-6C22.5 2.504 23.004 2 23.625 2h2.25C26.496 2 27 2.504 27 3.125v16.75c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 0 1 22.5 19.875V3.125Z"></path>
              </svg>
              <span class="side-menu__label">{{ $t('nav.monitoring') }}</span>
            </RouterLink>
          </li>

          <!-- Settings Category -->
          <li class="slide__category"><span class="category-name">{{ $t('nav.settings') }}</span></li>

          <!-- Settings -->
          <li class="slide" :class="{ 'active': $route.path === '/settings' }">
            <RouterLink to="/settings" class="side-menu__item">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 side-menu__icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
              </svg>
              <span class="side-menu__label">{{ $t('nav.settings') }}</span>
            </RouterLink>
          </li>
        </ul>
      </nav>
      <!-- End::nav -->
    </div>
    <!-- End::main-sidebar -->
  </aside>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>
