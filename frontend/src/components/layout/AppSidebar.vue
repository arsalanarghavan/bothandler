<template>
  <aside class="app-sidebar">
    <nav>
      <ul>
        <li>
          <RouterLink to="/">{{ $t('nav.dashboard') }}</RouterLink>
        </li>
        <li>
          <RouterLink to="/bots">{{ $t('nav.bots') }}</RouterLink>
        </li>
        <li>
          <RouterLink to="/services">{{ $t('nav.services') }}</RouterLink>
        </li>
        <li>
          <RouterLink to="/deploy-bot">{{ $t('nav.deployBot') }}</RouterLink>
        </li>
        <li>
          <RouterLink to="/settings">{{ $t('nav.settings') }}</RouterLink>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router';
</script>


