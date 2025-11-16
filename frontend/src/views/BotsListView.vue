<template>
  <section>
    <h1>Services</h1>
    <div style="margin-bottom: 0.75rem">
      <button type="button" @click="updateAll" :disabled="updatingAll">
        {{ updatingAll ? 'Updating all...' : 'Update All' }}
      </button>
      <button
        type="button"
        style="margin-inline-start: 0.5rem"
        @click="deleteAll"
        :disabled="deletingAll"
      >
        {{ deletingAll ? 'Deleting...' : 'Delete All' }}
      </button>
    </div>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Domain</th>
          <th>Status</th>
          <th>Actions</th>
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
          <td>
            <button type="button" @click="updateBot(bot.id)">Update</button>
            <button type="button" style="margin-inline-start: 0.25rem" @click="deleteBot(bot.id)">
              Delete
            </button>
          </td>
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
const updatingAll = ref(false);
const deletingAll = ref(false);

const load = async () => {
  const response = await axios.get(`${API_BASE}/bots`);
  bots.value = response.data.data;
};

onMounted(load);

const updateBot = async (id: number) => {
  await axios.post(`${API_BASE}/bots/${id}/deploy`);
};

const deleteBot = async (id: number) => {
  if (!window.confirm('Are you sure you want to delete this service?')) return;
  await axios.delete(`${API_BASE}/bots/${id}`);
  await load();
};

const updateAll = async () => {
  updatingAll.value = true;
  try {
    await axios.post(`${API_BASE}/bots/update-all`);
  } finally {
    updatingAll.value = false;
  }
};

const deleteAll = async () => {
  if (!window.confirm('Are you sure you want to delete ALL services?')) return;
  deletingAll.value = true;
  try {
    await axios.delete(`${API_BASE}/bots`);
    await load();
  } finally {
    deletingAll.value = false;
  }
};
</script>


