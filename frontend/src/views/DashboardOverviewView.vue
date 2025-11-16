<template>
  <section>
    <h1>{{ $t('dashboard.title') }}</h1>
    <div class="stats-grid">
      <div class="stat-card">
        <div class="label">{{ $t('dashboard.totalContainers') }}</div>
        <div class="value">{{ summary?.total ?? '-' }}</div>
      </div>
      <div class="stat-card">
        <div class="label">{{ $t('dashboard.runningContainers') }}</div>
        <div class="value">{{ summary?.running ?? '-' }}</div>
      </div>
      <div class="stat-card">
        <div class="label">{{ $t('dashboard.stoppedContainers') }}</div>
        <div class="value">{{ summary?.stopped ?? '-' }}</div>
      </div>
      <div class="stat-card">
        <div class="label">{{ $t('dashboard.avgCpu') }}</div>
        <div class="value">
          {{ summary?.avg_cpu_percent != null ? summary.avg_cpu_percent.toFixed(1) + '%' : '-' }}
        </div>
      </div>
      <div class="stat-card">
        <div class="label">{{ $t('dashboard.totalMemory') }}</div>
        <div class="value">
          <span v-if="summary?.total_mem_usage != null">
            {{ formatBytes(summary.total_mem_usage) }}
            <span v-if="summary.total_mem_percent != null">
              ({{ summary.total_mem_percent.toFixed(1) }}%)
            </span>
          </span>
          <span v-else>-</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import axios from 'axios';

interface Summary {
  total: number;
  running: number;
  stopped: number;
  avg_cpu_percent: number | null;
  total_mem_usage: number | null;
  total_mem_limit: number | null;
  total_mem_percent: number | null;
  total_net_input?: number | null;
  total_net_output?: number | null;
}

const summary = ref<Summary | null>(null);

const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api';

onMounted(async () => {
  const response = await axios.get(`${API_BASE}/dashboard/summary`);
  summary.value = response.data.data;
});

const formatBytes = (value?: number | null): string => {
  if (value == null) return '-';
  const units = ['B', 'KB', 'MB', 'GB', 'TB'];
  let v = value;
  let i = 0;
  while (v >= 1024 && i < units.length - 1) {
    v /= 1024;
    i++;
  }
  return `${v.toFixed(1)} ${units[i]}`;
};
</script>


