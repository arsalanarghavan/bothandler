<template>
  <section v-if="bot">
    <h1>{{ bot.name }}</h1>
    <p><strong>Domain:</strong> {{ bot.domain || '-' }}</p>
    <p><strong>Status:</strong> {{ bot.status }}</p>

    <button @click="deploy" :disabled="deploying">
      {{ deploying ? 'Deploying...' : 'Deploy' }}
    </button>

    <h2>Deployments</h2>
    <ul>
      <li v-for="deployment in deployments" :key="deployment.id">
        <button type="button" @click="selectDeployment(deployment.id)">
          {{ deployment.created_at }} - {{ deployment.status }}
        </button>
      </li>
    </ul>

    <div v-if="activeLog" class="terminal">
      <h3>Deployment Log #{{ activeDeploymentId }}</h3>
      <pre>{{ activeLog }}</pre>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

interface Bot {
  id: number;
  name: string;
  domain: string;
  status: string;
}

interface Deployment {
  id: number;
  status: string;
  created_at: string;
}

const route = useRoute();
const bot = ref<Bot | null>(null);
const deployments = ref<Deployment[]>([]);
const deploying = ref(false);
const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api';
const activeDeploymentId = ref<number | null>(null);
const activeLog = ref<string>('');
let logInterval: number | undefined;

const load = async () => {
  const id = route.params.id;
  const response = await axios.get(`${API_BASE}/bots/${id}`);
  bot.value = response.data.data;
  deployments.value = response.data.data.deployments ?? [];
};

const deploy = async () => {
  if (!bot.value) return;
  deploying.value = true;
  try {
    await axios.post(`${API_BASE}/bots/${bot.value.id}/deploy`);
    await load();
  } finally {
    deploying.value = false;
  }
};

onMounted(load);

const selectDeployment = async (id: number) => {
  activeDeploymentId.value = id;
  await fetchLog();
  if (logInterval) {
    window.clearInterval(logInterval);
  }
  logInterval = window.setInterval(fetchLog, 2000);
};

const fetchLog = async () => {
  if (!activeDeploymentId.value) return;
  const response = await axios.get(`${API_BASE}/deployments/${activeDeploymentId.value}`);
  activeLog.value = response.data.data.log ?? '';
};
</script>


