<template>
  <footer class="footer mt-auto py-3 bg-white text-center">
    <div class="container">
      <span class="text-muted">
        <span>{{ currentYear }}</span>
        {{ $t('footer.designWith') }}
        <span class="bi bi-heart-fill text-danger"></span>
        {{ $t('footer.by') }}
        <a href="https://www.rtl-theme.com/author/hogondesign/products/" target="_blank">
          <span class="fw-medium text-primary">Xintra</span>
        </a>
      </span>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const currentYear = computed(() => new Date().getFullYear());
</script>
