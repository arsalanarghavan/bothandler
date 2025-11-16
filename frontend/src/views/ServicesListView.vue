<template>
  <section>
    <h1>Services</h1>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Names</th>
          <th>Image</th>
          <th>State</th>
          <th>CPU %</th>
          <th>Memory</th>
          <th>Net In / Out</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="container in containers" :key="container.id">
          <td>{{ container.id }}</td>
          <td>{{ container.names.join(', ') }}</td>
          <td>{{ container.image }}</td>
          <td>{{ container.state }}</td>
          <td>{{ container.cpu_percent != null ? container.cpu_percent.toFixed(1) : '-' }}</td>
          <td>
            <span v-if="container.mem_usage != null">
              {{ formatBytes(container.mem_usage) }} / {{ formatBytes(container.mem_limit) }}
              <span v-if="container.mem_percent != null">({{ container.mem_percent.toFixed(1) }}%)</span>
            </span>
            <span v-else>-</span>
          </td>
          <td>
            <span v-if="container.net_input != null">
              {{ formatBytes(container.net_input) }} / {{ formatBytes(container.net_output) }}
            </span>
            <span v-else>-</span>
          </td>
        </tr>
      </tbody>
    </table>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import axios from 'axios';

interface Container {
  id: string;
  names: string[];
  image: string;
  state: string;
  cpu_percent: number | null;
  mem_usage: number | null;
  mem_limit: number | null;
  mem_percent: number | null;
  net_input: number | null;
  net_output: number | null;
}

const containers = ref<Container[]>([]);
const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api';

onMounted(async () => {
  const response = await axios.get(`${API_BASE}/dashboard/containers`);
  containers.value = response.data.data;
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


