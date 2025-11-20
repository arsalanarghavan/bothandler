<template>
  <div class="flex min-h-svh flex-col items-center justify-center bg-muted p-6 md:p-10">
    <div class="w-full max-w-lg">
      <Card>
        <CardHeader>
          <CardTitle>Setup Dashboard</CardTitle>
          <CardDescription>Step {{ currentStep + 1 }} of 4</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="handleNext" class="space-y-6">
            <!-- Step 1: Dashboard Info -->
            <div v-if="currentStep === 0" class="space-y-4">
              <div class="space-y-2">
                <Label for="dashboard_name">Dashboard Name *</Label>
                <Input 
                  id="dashboard_name"
                  v-model="form.dashboard_name" 
                  placeholder="My Dashboard"
                  required
                />
              </div>
              <div class="space-y-2">
                <Label for="dashboard_domain">Dashboard Domain *</Label>
                <Input 
                  id="dashboard_domain"
                  v-model="form.dashboard_domain" 
                  placeholder="dashboard.example.com"
                  required
                />
                <p class="text-xs text-muted-foreground">Enter your domain name for SSL configuration</p>
              </div>
            </div>

            <!-- Step 2: Admin Account -->
            <div v-if="currentStep === 1" class="space-y-4">
              <div class="space-y-2">
                <Label for="email">Admin Email *</Label>
                <Input 
                  id="email"
                  v-model="form.email" 
                  type="email"
                  placeholder="admin@example.com"
                  required
                />
              </div>
              <div class="space-y-2">
                <Label for="username">Admin Username *</Label>
                <Input 
                  id="username"
                  v-model="form.username" 
                  placeholder="admin"
                  required
                />
              </div>
            </div>

            <!-- Step 3: Password -->
            <div v-if="currentStep === 2" class="space-y-4">
              <div class="space-y-2">
                <Label for="password">Password *</Label>
                <Input 
                  id="password"
                  v-model="form.password" 
                  type="password"
                  placeholder="Minimum 8 characters"
                  required
                  minlength="8"
                />
              </div>
              <div class="space-y-2">
                <Label for="password_confirmation">Confirm Password *</Label>
                <Input 
                  id="password_confirmation"
                  v-model="form.password_confirmation" 
                  type="password"
                  placeholder="Repeat password"
                  required
                />
              </div>
            </div>

            <!-- Step 4: Confirmation -->
            <div v-if="currentStep === 3" class="space-y-4 text-center">
              <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                <Check class="h-8 w-8 text-primary" />
              </div>
              <div>
                <h3 class="text-lg font-semibold">Ready to Install</h3>
                <p class="text-sm text-muted-foreground">Please review your information and click Install</p>
              </div>
              <Card>
                <CardContent class="space-y-2 pt-6 text-left">
                  <div class="flex justify-between">
                    <span class="text-sm font-medium">Dashboard Name:</span>
                    <span class="text-sm text-muted-foreground">{{ form.dashboard_name }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm font-medium">Domain:</span>
                    <span class="text-sm text-muted-foreground">{{ form.dashboard_domain }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm font-medium">Email:</span>
                    <span class="text-sm text-muted-foreground">{{ form.email }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm font-medium">Username:</span>
                    <span class="text-sm text-muted-foreground">{{ form.username }}</span>
                  </div>
                </CardContent>
              </Card>
            </div>

            <div v-if="error" class="rounded-lg bg-destructive/15 p-3 text-sm text-destructive">
              {{ error }}
            </div>
          </form>
        </CardContent>
        <CardFooter class="flex justify-between">
          <Button 
            v-if="currentStep > 0" 
            @click="prevStep" 
            variant="outline"
            :disabled="submitting"
          >
            Previous
          </Button>
          <div v-else></div>
          <Button 
            v-if="currentStep < 3" 
            @click="nextStep"
            :disabled="submitting"
          >
            Next
          </Button>
          <Button 
            v-else
            @click="submit"
            :disabled="submitting"
          >
            <span v-if="submitting">Installing...</span>
            <span v-else>Complete Installation</span>
          </Button>
        </CardFooter>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/lib/api'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Check } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  dashboard_name: '',
  dashboard_domain: '',
  email: '',
  username: '',
  password: '',
  password_confirmation: '',
})

const currentStep = ref(0)
const submitting = ref(false)
const error = ref('')

const nextStep = () => {
  if (currentStep.value < 3) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

const submit = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }

  submitting.value = true
  error.value = ''
  try {
    const response = await apiClient.post('/setup/complete', form.value)
    console.log('Setup response:', response.data)
    
    // Update installation status in store and localStorage
    authStore.isInstalled = true
    localStorage.setItem('installation_status', JSON.stringify(true))
    
    // Show success message briefly before redirect
    setTimeout(() => {
      router.push('/login')
    }, 500)
  } catch (err: any) {
    console.error('Setup error:', err)
    
    // Show detailed error message
    if (err.data?.errors) {
      // Validation errors
      const errors = Object.values(err.data.errors).flat()
      error.value = errors.join(', ')
    } else if (err.message) {
      // Error message from interceptor
      error.value = err.message
    } else {
      error.value = 'Installation failed. Please check server logs and browser console.'
    }
  } finally {
    submitting.value = false
  }
}
</script>
