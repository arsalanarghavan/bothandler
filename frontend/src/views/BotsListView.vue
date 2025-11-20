<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Deployed Services</h1>
        <p class="text-muted-foreground">Manage your deployed bots and services</p>
      </div>
      <div class="flex gap-2">
        <Button @click="updateAll" variant="outline" :disabled="loading || bots.length === 0">
          <RefreshCw :class="['h-4 w-4 mr-2', loading && 'animate-spin']" />
          Update All
        </Button>
        <Button @click="router.push('/deploy-bot')">
          <PlusCircle class="h-4 w-4 mr-2" />
          Deploy New
        </Button>
      </div>
    </div>

    <Card>
      <CardContent class="p-0">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Name</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Repository</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Last Deployed</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading && bots.length === 0">
              <TableCell colspan="6" class="text-center py-8">
                <div class="flex items-center justify-center gap-2">
                  <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                  <span>Loading services...</span>
                </div>
              </TableCell>
            </TableRow>
            
            <TableRow v-else-if="!loading && bots.length === 0">
              <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                <div class="space-y-2">
                  <p>No services deployed yet</p>
                  <Button @click="router.push('/deploy-bot')" variant="outline" size="sm">
                    Deploy your first service
                  </Button>
                </div>
              </TableCell>
            </TableRow>

            <TableRow v-for="bot in bots" :key="bot.id">
              <TableCell class="font-medium">
                <RouterLink :to="`/bots/${bot.id}`" class="hover:underline">
                  {{ bot.name }}
                </RouterLink>
              </TableCell>
              <TableCell>
                <Badge variant="outline">{{ bot.service_type || 'generic' }}</Badge>
              </TableCell>
              <TableCell>
                <code class="text-xs bg-muted px-2 py-1 rounded">
                  {{ formatRepo(bot.github_repo_url) }}
                </code>
              </TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(bot.status)">
                  {{ bot.status }}
                </Badge>
              </TableCell>
              <TableCell class="text-sm text-muted-foreground">
                {{ formatDate(bot.last_deployed_at) }}
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button 
                    @click="deployBot(bot.id)" 
                    size="sm" 
                    variant="outline"
                    :disabled="deploying[bot.id]"
                  >
                    <RefreshCw :class="['h-4 w-4', deploying[bot.id] && 'animate-spin']" />
                  </Button>
                  <Button 
                    @click="deleteBot(bot.id)" 
                    size="sm" 
                    variant="destructive"
                    :disabled="deleting[bot.id]"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { PlusCircle, RefreshCw, Trash2 } from 'lucide-vue-next'

const router = useRouter()
const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api'

const bots = ref<any[]>([])
const loading = ref(false)
const deploying = reactive<Record<number, boolean>>({})
const deleting = reactive<Record<number, boolean>>({})

const formatRepo = (url: string) => {
  if (!url) return 'N/A'
  return url.replace('https://github.com/', '').replace('.git', '')
}

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

const loadBots = async () => {
  loading.value = true
  try {
    const response = await axios.get(`${API_BASE}/bots`)
    bots.value = response.data
  } catch (error) {
    console.error('Failed to load bots:', error)
  } finally {
    loading.value = false
  }
}

const deployBot = async (id: number) => {
  deploying[id] = true
  try {
    await axios.post(`${API_BASE}/bots/${id}/deploy`)
    await loadBots()
  } catch (error) {
    console.error('Failed to deploy bot:', error)
  } finally {
    deploying[id] = false
  }
}

const deleteBot = async (id: number) => {
  if (!confirm('Are you sure you want to delete this service?')) return
  
  deleting[id] = true
  try {
    await axios.delete(`${API_BASE}/bots/${id}`)
    await loadBots()
  } catch (error) {
    console.error('Failed to delete bot:', error)
  } finally {
    deleting[id] = false
  }
}

const updateAll = async () => {
  if (!confirm('Update all services? This may take a while.')) return
  
  loading.value = true
  try {
    await axios.post(`${API_BASE}/bots/update-all`)
    await loadBots()
  } catch (error) {
    console.error('Failed to update all:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadBots()
})
</script>
