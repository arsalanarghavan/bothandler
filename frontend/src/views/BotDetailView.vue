<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button @click="router.push('/bots')" variant="outline" size="icon">
          <ArrowLeft class="h-4 w-4" />
        </Button>
        <div>
          <h1 class="text-3xl font-bold tracking-tight">{{ bot.name }}</h1>
          <p class="text-muted-foreground">Service details and deployment logs</p>
        </div>
      </div>
      <Button @click="deployNow" :disabled="deploying">
        <RefreshCw :class="['h-4 w-4 mr-2', deploying && 'animate-spin']" />
        Redeploy
      </Button>
    </div>

    <!-- Service Info -->
    <Card>
      <CardHeader>
        <CardTitle>Service Information</CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <Label class="text-muted-foreground">Status</Label>
            <div class="mt-1">
              <Badge :variant="getStatusVariant(bot.status)">{{ bot.status }}</Badge>
            </div>
          </div>
          <div>
            <Label class="text-muted-foreground">Type</Label>
            <div class="mt-1">
              <Badge variant="outline">{{ bot.service_type || 'generic' }}</Badge>
            </div>
          </div>
          <div>
            <Label class="text-muted-foreground">Repository</Label>
            <p class="text-sm mt-1">
              <code class="bg-muted px-2 py-1 rounded">{{ bot.github_repo_url }}</code>
            </p>
          </div>
          <div>
            <Label class="text-muted-foreground">Branch</Label>
            <p class="text-sm mt-1">{{ bot.github_branch || 'main' }}</p>
          </div>
          <div v-if="bot.domain">
            <Label class="text-muted-foreground">Domain</Label>
            <p class="text-sm mt-1">
              <a :href="`https://${bot.domain}`" target="_blank" class="text-primary hover:underline">
                {{ bot.domain }}
              </a>
            </p>
          </div>
          <div>
            <Label class="text-muted-foreground">Last Deployed</Label>
            <p class="text-sm mt-1">{{ formatDate(bot.last_deployed_at) }}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Deployments -->
    <Card>
      <CardHeader>
        <CardTitle>Deployment History</CardTitle>
        <CardDescription>Recent deployments and their logs</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="loading" class="text-center py-8">
          <div class="flex items-center justify-center gap-2">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            <span>Loading deployments...</span>
          </div>
        </div>
        
        <div v-else-if="deployments.length === 0" class="text-center py-8 text-muted-foreground">
          No deployments yet
        </div>

        <div v-else class="space-y-4">
          <div 
            v-for="deployment in deployments" 
            :key="deployment.id"
            class="border rounded-lg p-4"
          >
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <Badge :variant="getDeploymentVariant(deployment.status)">
                  {{ deployment.status }}
                </Badge>
                <span class="text-sm text-muted-foreground">
                  {{ formatDate(deployment.started_at) }}
                </span>
              </div>
              <Button 
                @click="toggleLog(deployment.id)" 
                variant="ghost" 
                size="sm"
              >
                <ChevronDown 
                  :class="['h-4 w-4 transition-transform', expandedLogs[deployment.id] && 'rotate-180']" 
                />
              </Button>
            </div>
            
            <div v-if="expandedLogs[deployment.id]" class="mt-4">
              <div class="bg-black text-green-400 p-4 rounded-lg font-mono text-xs overflow-x-auto max-h-96 overflow-y-auto">
                <pre v-if="deployment.log">{{ deployment.log }}</pre>
                <p v-else class="text-gray-500">No logs available</p>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { ArrowLeft, RefreshCw, ChevronDown } from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const API_BASE = import.meta.env.VITE_API_BASE_URL || 'https://api.example.com/api'

const bot = ref<any>({})
const deployments = ref<any[]>([])
const loading = ref(false)
const deploying = ref(false)
const expandedLogs = reactive<Record<number, boolean>>({})

const formatDate = (date: string) => {
  if (!date) return 'Never'
  return new Date(date).toLocaleString()
}

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'active': return 'default'
    case 'error': return 'destructive'
    case 'pending': return 'secondary'
    default: return 'outline'
  }
}

const getDeploymentVariant = (status: string) => {
  switch (status) {
    case 'success': return 'default'
    case 'failed': return 'destructive'
    case 'queued': return 'secondary'
    default: return 'outline'
  }
}

const toggleLog = (id: number) => {
  expandedLogs[id] = !expandedLogs[id]
}

const loadBot = async () => {
  try {
    const response = await axios.get(`${API_BASE}/bots/${route.params.id}`)
    bot.value = response.data
  } catch (error) {
    console.error('Failed to load bot:', error)
  }
}

const loadDeployments = async () => {
  loading.value = true
  try {
    const response = await axios.get(`${API_BASE}/bots/${route.params.id}/deployments`)
    deployments.value = response.data
  } catch (error) {
    console.error('Failed to load deployments:', error)
  } finally {
    loading.value = false
  }
}

const deployNow = async () => {
  deploying.value = true
  try {
    await axios.post(`${API_BASE}/bots/${route.params.id}/deploy`)
    await loadBot()
    await loadDeployments()
  } catch (error) {
    console.error('Failed to deploy:', error)
  } finally {
    deploying.value = false
  }
}

onMounted(async () => {
  await loadBot()
  await loadDeployments()
})
</script>
