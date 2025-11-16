<template>
  <section>
    <h1>Deploy Service</h1>
    <form @submit.prevent="submit">
      <div>
        <label>Name</label>
        <input v-model="form.name" required />
      </div>
      <div>
        <label>GitHub Repo URL</label>
        <input v-model="form.github_repo_url" required />
      </div>
      <div>
        <label>Branch</label>
        <input v-model="form.github_branch" />
      </div>
      <div>
        <label>Domain</label>
        <input v-model="form.domain" required />
      </div>
      <div>
        <label>Custom deploy command (optional)</label>
        <input v-model="form.deploy_command" placeholder="مثلاً: docker compose up -d --build" />
      </div>
      <button type="submit" :disabled="submitting">
        {{ submitting ? 'Submitting...' : 'Create Bot' }}
      </button>
    </form>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

const form = ref({
  name: '',
  github_repo_url: '',
  github_branch: 'main',
  domain: '',
  deploy_command: '',
});

const submitting = ref(false);
const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api';

const submit = async () => {
  submitting.value = true;
  try {
    await axios.post(`${API_BASE}/bots`, form.value);
    router.push('/bots');
  } finally {
    submitting.value = false;
  }
};
</script>


