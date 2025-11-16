<template>
  <section class="wizard">
    <h1>نصب اولیه داشبورد</h1>
    <form @submit.prevent="submit">
      <div>
        <label>نام داشبورد</label>
        <input v-model="form.dashboard_name" required />
      </div>
      <div>
        <label>ایمیل ادمین</label>
        <input v-model="form.email" type="email" required />
      </div>
      <div>
        <label>نام کاربری ادمین</label>
        <input v-model="form.username" required />
      </div>
      <div>
        <label>رمز عبور</label>
        <input v-model="form.password" type="password" required />
      </div>
      <div>
        <label>تکرار رمز عبور</label>
        <input v-model="form.password_confirmation" type="password" required />
      </div>
      <button type="submit" :disabled="submitting">
        {{ submitting ? 'در حال نصب...' : 'اتمام نصب' }}
      </button>
    </form>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api';

const form = ref({
  dashboard_name: '',
  email: '',
  username: '',
  password: '',
  password_confirmation: '',
});

const submitting = ref(false);

const submit = async () => {
  submitting.value = true;
  try {
    await axios.post(`${API_BASE}/setup/complete`, form.value);
    router.push('/');
  } finally {
    submitting.value = false;
  }
};
</script>


