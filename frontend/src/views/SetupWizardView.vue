<template>
  <div class="page">
    <div class="main-content app-content">
      <div class="side-app">
        <div class="main-container container-fluid">
          <div class="row justify-content-center">
            <div class="col-xl-8">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">{{ $t('setup.title') }}</h3>
                </div>
                <div class="card-body">
                  <form class="wizard wizard-tab horizontal" @submit.prevent="submit" id="setup-wizard">
              <aside class="wizard-content container">
                <!-- Step 1: Dashboard Info -->
                <div class="wizard-step active" data-title="اطلاعات داشبورد">
                  <div class="row">
                    <div class="col-xl-12">
                      <div class="mb-3">
                        <label class="form-label">{{ $t('setup.dashboardName') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" v-model="form.dashboard_name" required placeholder="نام داشبورد">
                      </div>
                    </div>
                    <div class="col-xl-12">
                      <div class="mb-3">
                        <label class="form-label">{{ $t('setup.dashboardDomain') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" v-model="form.dashboard_domain" required placeholder="dashboard.example.com">
                        <small class="text-muted">{{ $t('setup.domainHint') }}</small>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 2: Admin Account -->
                <div class="wizard-step" data-title="حساب ادمین">
                  <div class="row">
                    <div class="col-xl-12">
                      <div class="mb-3">
                        <label class="form-label">{{ $t('setup.adminEmail') }} <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" v-model="form.email" required placeholder="admin@example.com">
                      </div>
                    </div>
                    <div class="col-xl-12">
                      <div class="mb-3">
                        <label class="form-label">{{ $t('setup.adminUsername') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" v-model="form.username" required placeholder="نام کاربری">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 3: Password -->
                <div class="wizard-step" data-title="رمز عبور">
                  <div class="row">
                    <div class="col-xl-12">
                      <div class="mb-3">
                        <label class="form-label">{{ $t('setup.password') }} <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" v-model="form.password" required placeholder="حداقل 8 کاراکتر">
                      </div>
                    </div>
                    <div class="col-xl-12">
                      <div class="mb-3">
                        <label class="form-label">{{ $t('setup.confirmPassword') }} <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" v-model="form.password_confirmation" required placeholder="تکرار رمز عبور">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 4: Confirmation -->
                <div class="wizard-step" data-title="تایید">
                  <div class="text-center">
                    <div class="mb-4">
                      <i class="ri-checkbox-circle-line text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4>{{ $t('setup.readyToInstall') }}</h4>
                    <p class="text-muted">{{ $t('setup.confirmMessage') }}</p>
                    <div class="card mt-4">
                      <div class="card-body text-start">
                        <p><strong>{{ $t('setup.dashboardName') }}:</strong> {{ form.dashboard_name }}</p>
                        <p><strong>{{ $t('setup.dashboardDomain') }}:</strong> {{ form.dashboard_domain }}</p>
                        <p><strong>{{ $t('setup.adminEmail') }}:</strong> {{ form.email }}</p>
                        <p><strong>{{ $t('setup.adminUsername') }}:</strong> {{ form.username }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </aside>

              <!-- Wizard Navigation -->
              <div class="d-flex wizard justify-content-between mt-3 flex-wrap gap-2">
                <button type="button" class="btn btn-light wizard-prev" @click="prevStep">
                  <i class="ri-arrow-right-line me-1"></i>{{ $t('setup.previous') }}
                </button>
                <button type="button" class="btn btn-primary wizard-next" @click="nextStep" v-if="currentStep < 3">
                  {{ $t('setup.next') }}<i class="ri-arrow-left-line ms-1"></i>
                </button>
                <button type="submit" class="btn btn-primary" v-if="currentStep === 3" :disabled="submitting">
                  <span v-if="submitting">
                    <span class="spinner-border spinner-border-sm me-1"></span>{{ $t('setup.installing') }}
                  </span>
                  <span v-else>
                    {{ $t('setup.completeInstallation') }}<i class="ri-check-line ms-1"></i>
                  </span>
                </button>
              </div>
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
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const router = useRouter();
const { t } = useI18n();
const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api';

const form = ref({
  dashboard_name: '',
  dashboard_domain: '',
  email: '',
  username: '',
  password: '',
  password_confirmation: '',
});

const submitting = ref(false);
const currentStep = ref(0);

const nextStep = () => {
  if (currentStep.value < 3) {
    currentStep.value++;
    updateWizardSteps();
  }
};

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--;
    updateWizardSteps();
  }
};

const updateWizardSteps = () => {
  const steps = document.querySelectorAll('.wizard-step');
  steps.forEach((step, index) => {
    if (index === currentStep.value) {
      step.classList.add('active');
    } else {
      step.classList.remove('active');
    }
  });
};

const submit = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    alert(t('setup.passwordMismatch'));
    return;
  }

  submitting.value = true;
  try {
    await axios.post(`${API_BASE}/setup/complete`, form.value);
    router.push('/login');
  } catch (error: any) {
    alert(error.response?.data?.message || t('setup.error'));
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  updateWizardSteps();
  
  // Initialize vanilla-wizard if available
  if (typeof window !== 'undefined' && (window as any).VanillaWizard) {
    const wizardElement = document.getElementById('setup-wizard');
    if (wizardElement) {
      try {
        new (window as any).VanillaWizard(wizardElement);
      } catch (e) {
        console.warn('VanillaWizard initialization failed:', e);
      }
    }
  }
  
  // Also try to trigger form-wizard-init if it exists
  setTimeout(() => {
    if (typeof (window as any).formWizardInit === 'function') {
      (window as any).formWizardInit();
    }
  }, 100);
});
</script>

<style>
/* Remove scoped to allow global styles to apply */
.wizard-step {
  display: none;
}

.wizard-step.active {
  display: block;
}

.wizard-step[data-title]::before {
  content: attr(data-title);
  display: block;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--primary-color);
}

/* Ensure page has proper background */
.page {
  min-height: 100vh;
  background-color: #f5f7fb;
}

.main-content {
  margin-top: 0;
  padding-top: 2rem;
}
</style>
