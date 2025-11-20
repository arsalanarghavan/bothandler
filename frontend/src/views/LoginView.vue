<template>
  <div class="flex min-h-svh flex-col items-center justify-center bg-muted p-6 md:p-10">
    <div class="w-full max-w-sm">
      <Card>
        <CardHeader class="space-y-1">
          <CardTitle class="text-2xl">Login</CardTitle>
          <CardDescription>Enter your credentials to access the dashboard</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="handleLogin" class="space-y-4">
            <div class="space-y-2">
              <Label for="email">Email</Label>
              <Input 
                id="email" 
                v-model="form.email" 
                type="email" 
                placeholder="admin@example.com"
                required 
                :disabled="loading"
              />
            </div>
            <div class="space-y-2">
              <Label for="password">Password</Label>
              <Input 
                id="password"
                v-model="form.password" 
                type="password" 
                placeholder="••••••••"
                required
                :disabled="loading"
              />
            </div>
            <div v-if="error" class="rounded-lg bg-destructive/15 p-3 text-sm text-destructive">
              {{ error }}
            </div>
            <Button type="submit" class="w-full" :disabled="loading">
              <span v-if="loading">Logging in...</span>
              <span v-else>Login</span>
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
})

const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  try {
    await authStore.login(form.value.email, form.value.password)
    router.push('/')
  } catch (err: any) {
    error.value = err.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>
