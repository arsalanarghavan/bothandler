<template>
  <div class="page">
    <div class="main-content app-content">
      <div class="side-app">
        <div class="main-container container-fluid">
          <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">ورود به داشبورد</h3>
                </div>
                <div class="card-body">
                  <form @submit.prevent="handleLogin">
                    <div class="mb-3">
                      <label class="form-label">ایمیل</label>
                      <input v-model="form.email" type="email" class="form-control" required />
                    </div>
                    <div class="mb-3">
                      <label class="form-label">رمز عبور</label>
                      <input v-model="form.password" type="password" class="form-control" required />
                    </div>
                    <div v-if="error" class="alert alert-danger">{{ error }}</div>
                    <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                      {{ loading ? 'در حال ورود...' : 'ورود' }}
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  try {
    await authStore.login(form.value.email, form.value.password);
    router.push('/');
  } catch (err: any) {
    error.value = err.message || 'خطا در ورود';
  } finally {
    loading.value = false;
  }
};
</script>

<style>
.page {
  min-height: 100vh;
  background-color: #f5f7fb;
}

.main-content {
  margin-top: 0;
  padding-top: 2rem;
}
</style>

