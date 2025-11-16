<template>
  <section>
    <h1>Services</h1>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Domain</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="bot in bots" :key="bot.id">
          <td>{{ bot.id }}</td>
          <td>
            <RouterLink :to="`/bots/${bot.id}`">{{ bot.name }}</RouterLink>
          </td>
          <td>{{ bot.domain }}</td>
          <td>{{ bot.status }}</td>
        </tr>
      </tbody>
    </table>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';

interface Bot {
  id: number;
  name: string;
  domain: string;
  status: string;
}

const bots = ref<Bot[]>([]);
const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api';

onMounted(async () => {
  const response = await axios.get(`${API_BASE}/bots`);
  bots.value = response.data.data;
});
</script>


