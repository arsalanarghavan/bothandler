<template>
  <header class="app-header sticky" id="header">
    <div class="main-header-container container-fluid">
      <!-- Start::header-content-left -->
      <div class="header-content-left">
        <!-- Logo -->
        <div class="header-element">
          <div class="horizontal-logo">
            <RouterLink to="/" class="header-logo">
              <img src="/assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
              <img src="/assets/images/brand-logos/toggle-dark.png" alt="logo" class="toggle-dark">
              <img src="/assets/images/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
              <img src="/assets/images/brand-logos/toggle-logo.png" alt="logo" class="toggle-logo">
              <img src="/assets/images/brand-logos/toggle-white.png" alt="logo" class="toggle-white">
              <img src="/assets/images/brand-logos/desktop-white.png" alt="logo" class="desktop-white">
            </RouterLink>
          </div>
        </div>

        <!-- Sidebar Toggle -->
        <div class="header-element mx-lg-0 mx-2">
          <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" 
             @click="toggleSidebar" href="javascript:void(0);">
            <span></span>
          </a>
        </div>

        <!-- Search -->
        <div class="header-element header-search d-md-block d-none my-auto auto-complete-search">
          <input type="text" class="header-search-bar form-control" 
                 v-model="searchQuery" 
                 :placeholder="$t('header.search')" 
                 spellcheck="false" autocomplete="off" autocapitalize="off">
          <a href="javascript:void(0);" class="header-search-icon border-0">
            <i class="ri-search-line"></i>
          </a>
        </div>
      </div>
      <!-- End::header-content-left -->

      <!-- Start::header-content-right -->
      <ul class="header-content-right">
        <!-- Mobile Search -->
        <li class="header-element d-md-none d-block">
          <a href="javascript:void(0);" class="header-link" @click="showMobileSearch = !showMobileSearch">
            <i class="bi bi-search header-link-icon d-flex"></i>
          </a>
        </li>

        <!-- Language Selector -->
        <li class="header-element country-selector dropdown">
          <a href="javascript:void(0);" class="header-link dropdown-toggle" 
             data-bs-auto-close="outside" 
             data-bs-toggle="dropdown"
             ref="langDropdown">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 header-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"></path>
            </svg>
          </a>
          <ul class="main-header-dropdown dropdown-menu dropdown-menu-end">
            <li v-for="lang in languages" :key="lang.code">
              <a class="dropdown-item d-flex align-items-center" 
                 href="javascript:void(0);"
                 @click="changeLanguage(lang.code)">
                <span class="avatar avatar-rounded avatar-xs lh-1 me-2">
                  <img :src="lang.flag" :alt="lang.name">
                </span>
                {{ lang.name }}
              </a>
            </li>
          </ul>
        </li>

        <!-- Theme Toggle -->
        <li class="header-element header-theme-mode">
          <a href="javascript:void(0);" class="header-link layout-setting" @click="toggleTheme">
            <span class="light-layout" v-if="isDark">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 header-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"></path>
              </svg>
            </span>
            <span class="dark-layout" v-else>
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 header-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"></path>
              </svg>
            </span>
          </a>
        </li>

        <!-- Notifications -->
        <li class="header-element notifications-dropdown d-xl-block d-none dropdown">
          <a href="javascript:void(0);" class="header-link dropdown-toggle" 
             data-bs-toggle="dropdown" 
             data-bs-auto-close="outside">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 header-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5"></path>
            </svg>
            <span class="header-icon-pulse bg-primary2 rounded pulse pulse-secondary"></span>
          </a>
          <div class="main-header-dropdown dropdown-menu dropdown-menu-end">
            <div class="p-3">
              <div class="d-flex align-items-center justify-content-between">
                <p class="mb-0 fs-15 fw-medium">{{ $t('header.notifications') }}</p>
                <span class="badge bg-secondary text-fixed-white">{{ notifications.length }} {{ $t('header.unread') }}</span>
              </div>
            </div>
            <div class="dropdown-divider"></div>
            <ul class="list-unstyled mb-0">
              <li class="dropdown-item" v-for="notif in notifications" :key="notif.id">
                <div class="d-flex align-items-center">
                  <div class="pe-2 lh-1">
                    <span class="avatar avatar-md avatar-rounded" :class="notif.bgClass">
                      <img v-if="notif.image" :src="notif.image" :alt="notif.title">
                      <i v-else :class="notif.icon"></i>
                    </span>
                  </div>
                  <div class="flex-grow-1 d-flex align-items-center justify-content-between">
                    <div>
                      <p class="mb-0 fw-medium"><a href="javascript:void(0);">{{ notif.title }}</a></p>
                      <div class="text-muted fw-normal fs-12 header-notification-text text-truncate">{{ notif.message }}</div>
                      <div class="fw-normal fs-10 text-muted op-8">{{ notif.time }}</div>
                    </div>
                    <div>
                      <a href="javascript:void(0);" class="min-w-fit-content dropdown-item-close1" @click="removeNotification(notif.id)">
                        <i class="ri-close-line"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
            <div class="p-3 empty-header-item1 border-top" v-if="notifications.length > 0">
              <div class="d-grid">
                <a href="javascript:void(0);" class="btn btn-primary btn-wave">{{ $t('header.viewAll') }}</a>
              </div>
            </div>
            <div class="p-5 empty-item1" v-if="notifications.length === 0">
              <div class="text-center">
                <span class="avatar avatar-xl avatar-rounded bg-secondary-transparent">
                  <i class="ri-notification-off-line fs-2"></i>
                </span>
                <h6 class="fw-medium mt-3">{{ $t('header.noNotifications') }}</h6>
              </div>
            </div>
          </div>
        </li>

        <!-- Fullscreen -->
        <li class="header-element header-fullscreen">
          <a href="javascript:void(0);" class="header-link" @click="toggleFullscreen">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 full-screen-open header-link-icon" 
                 :class="{ 'd-none': isFullscreen }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"></path>
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 full-screen-close header-link-icon" 
                 :class="{ 'd-none': !isFullscreen }" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9 3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5 5.25 5.25"></path>
            </svg>
          </a>
        </li>

        <!-- Profile -->
        <li class="header-element dropdown">
          <a href="javascript:void(0);" class="header-link dropdown-toggle" 
             id="mainHeaderProfile" 
             data-bs-toggle="dropdown" 
             data-bs-auto-close="outside">
            <div class="d-flex align-items-center">
              <div>
                <img :src="userAvatar" alt="user" class="avatar avatar-sm">
              </div>
            </div>
          </a>
          <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end">
            <li>
              <div class="dropdown-item text-center border-bottom">
                <span>{{ authStore.user?.name || 'User' }}</span>
                <span class="d-block fs-12 text-muted">{{ authStore.user?.email || '' }}</span>
              </div>
            </li>
            <li>
              <RouterLink class="dropdown-item d-flex align-items-center" to="/settings">
                <i class="fe fe-settings p-1 rounded-circle bg-primary-transparent me-2 fs-16"></i>
                {{ $t('header.settings') }}
              </RouterLink>
            </li>
            <li class="border-top bg-light">
              <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);" @click="handleLogout">
                <i class="fe fe-lock p-1 rounded-circle bg-primary-transparent me-2 fs-16"></i>
                {{ $t('header.logout') }}
              </a>
            </li>
          </ul>
        </li>

        <!-- Switcher -->
        <li class="header-element">
          <a href="javascript:void(0);" class="header-link switcher-icon" 
             data-bs-toggle="offcanvas" 
             data-bs-target="#switcher-canvas">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 header-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
            </svg>
          </a>
        </li>
      </ul>
      <!-- End::header-content-right -->
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';

const { locale, t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();

const searchQuery = ref('');
const showMobileSearch = ref(false);
const isDark = ref(localStorage.getItem('theme') === 'dark');
const isFullscreen = ref(false);
const notifications = ref<any[]>([]);
const langDropdown = ref<HTMLElement | null>(null);

const userAvatar = computed(() => {
  return '/assets/images/faces/15.jpg'; // Default avatar
});

const languages = [
  { code: 'fa', name: 'فارسی', flag: '/assets/images/flags/uae_flag.jpg' },
  { code: 'en', name: 'انگلیسی', flag: '/assets/images/flags/us_flag.jpg' },
];

const toggleSidebar = () => {
  document.body.classList.toggle('sidebar-toggle');
};

const changeLanguage = (code: string) => {
  locale.value = code;
  localStorage.setItem('locale', code);
};

const toggleTheme = () => {
  isDark.value = !isDark.value;
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
  document.documentElement.setAttribute('data-theme-mode', isDark.value ? 'dark' : 'light');
};

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
    isFullscreen.value = true;
  } else {
    document.exitFullscreen();
    isFullscreen.value = false;
  }
};

const removeNotification = (id: number) => {
  notifications.value = notifications.value.filter(n => n.id !== id);
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

onMounted(() => {
  // Initialize theme
  document.documentElement.setAttribute('data-theme-mode', isDark.value ? 'dark' : 'light');
  
  // Load notifications (mock data for now)
  notifications.value = [];
});
</script>
