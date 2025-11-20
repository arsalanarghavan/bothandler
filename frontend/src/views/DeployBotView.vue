<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Deploy New Service</h1>
      <p class="text-muted-foreground">Deploy a bot or service from a GitHub repository</p>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Service Configuration</CardTitle>
        <CardDescription>Fill in the details below to deploy your service</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="name">Service Name *</Label>
              <Input 
                id="name" 
                v-model="form.name" 
                placeholder="my-awesome-bot"
                required 
              />
              <p class="text-xs text-muted-foreground">A unique name for your service</p>
            </div>

            <div class="space-y-2">
              <Label for="service_type">Service Type</Label>
              <Select v-model="form.service_type">
                <SelectTrigger id="service_type">
                  <SelectValue placeholder="Select type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="web">Web Application</SelectItem>
                  <SelectItem value="bot">Bot</SelectItem>
                  <SelectItem value="cron">Cron Job</SelectItem>
                  <SelectItem value="worker">Background Worker</SelectItem>
                  <SelectItem value="api">API Service</SelectItem>
                  <SelectItem value="generic">Generic</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="github_repo_url">GitHub Repository URL *</Label>
            <Input 
              id="github_repo_url" 
              v-model="form.github_repo_url" 
              placeholder="https://github.com/username/repo"
              required
            />
            <p class="text-xs text-muted-foreground">Full URL to your GitHub repository</p>
          </div>

          <div class="grid gap-6 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="github_branch">Branch</Label>
              <Input 
                id="github_branch" 
                v-model="form.github_branch" 
                placeholder="main"
              />
              <p class="text-xs text-muted-foreground">Leave empty for default branch</p>
            </div>

            <div class="space-y-2">
              <Label for="domain">Domain (Optional)</Label>
              <Input 
                id="domain" 
                v-model="form.domain" 
                placeholder="mybot.example.com"
              />
              <p class="text-xs text-muted-foreground">For web services with SSL</p>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="deploy_command">Custom Deploy Command (Optional)</Label>
            <Input 
              id="deploy_command" 
              v-model="form.deploy_command" 
              placeholder="docker-compose up -d --build"
            />
            <p class="text-xs text-muted-foreground">
              Leave empty to auto-detect (docker-compose.yml or Dockerfile)
            </p>
          </div>

          <Separator />

          <div v-if="error" class="rounded-lg bg-destructive/15 p-3 text-sm text-destructive">
            {{ error }}
          </div>

          <div class="flex gap-2 justify-end">
            <Button type="button" variant="outline" @click="router.push('/bots')">
              Cancel
            </Button>
            <Button type="submit" :disabled="submitting">
              <span v-if="submitting" class="flex items-center gap-2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-current"></div>
                Deploying...
              </span>
              <span v-else class="flex items-center gap-2">
                <Rocket class="h-4 w-4" />
                Deploy Service
              </span>
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Separator } from '@/components/ui/separator'
import { Rocket } from 'lucide-vue-next'

const router = useRouter()
const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api'

const form = ref({
  name: '',
  service_type: 'generic',
  github_repo_url: '',
  github_branch: '',
  domain: '',
  deploy_command: '',
})

const submitting = ref(false)
const error = ref('')

const handleSubmit = async () => {
  submitting.value = true
  error.value = ''
  
  try {
    const response = await axios.post(`${API_BASE}/bots`, form.value)
    router.push(`/bots/${response.data.id}`)
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to deploy service'
  } finally {
    submitting.value = false
  }
}
</script>
