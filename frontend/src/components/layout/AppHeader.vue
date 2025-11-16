<template>
  <header class="app-header">
    <div class="logo">Bot Dashboard</div>
    <div class="header-actions">
      <select v-model="currentLocale" @change="changeLocale">
        <option value="fa">فارسی</option>
        <option value="en">English</option>
      </select>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();

const currentLocale = computed({
  get: () => locale.value,
  set: (value: string) => {
    locale.value = value;
    localStorage.setItem('locale', value);
  },
});

const changeLocale = () => {
  // handled by computed setter
};
</script>


