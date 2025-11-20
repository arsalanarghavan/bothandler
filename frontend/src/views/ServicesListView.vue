<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Docker Containers</h1>
        <p class="text-muted-foreground">All running and stopped containers on this server</p>
      </div>
      <Button @click="loadContainers" :disabled="loading">
        <RefreshCw :class="['h-4 w-4 mr-2', loading && 'animate-spin']" />
        Refresh
      </Button>
    </div>

    <Card>
      <CardContent class="p-0">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Name</TableHead>
              <TableHead>Image</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="text-right">CPU %</TableHead>
              <TableHead class="text-right">Memory</TableHead>
              <TableHead class="text-right">Network I/O</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading && containers.length === 0">
              <TableCell colspan="6" class="text-center py-8">
                <div class="flex items-center justify-center gap-2">
                  <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                  <span>Loading containers...</span>
                </div>
              </TableCell>
            </TableRow>
            
            <TableRow v-else-if="!loading && containers.length === 0">
              <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                No containers found
              </TableCell>
            </TableRow>

            <TableRow v-for="container in containers" :key="container.id">
              <TableCell class="font-medium">
                <div>{{ formatName(container.names) }}</div>
              </TableCell>
              <TableCell>
                <code class="text-xs bg-muted px-2 py-1 rounded">{{ container.image }}</code>
              </TableCell>
              <TableCell>
                <Badge :variant="container.state === 'running' ? 'default' : 'secondary'">
                  <div class="flex items-center gap-1">
                    <div 
                      :class="[
                        'h-2 w-2 rounded-full',
                        container.state === 'running' ? 'bg-green-500' : 'bg-gray-400'
                      ]"
                    ></div>
                    {{ container.state }}
                  </div>
                </Badge>
              </TableCell>
              <TableCell class="text-right tabular-nums">
                {{ formatCpu(container.cpu_percent) }}
              </TableCell>
              <TableCell class="text-right tabular-nums">
                <div class="text-sm">
                  {{ formatBytes(container.mem_usage) }}
                  <span class="text-muted-foreground">/ {{ formatBytes(container.mem_limit) }}</span>
                </div>
                <div class="text-xs text-muted-foreground">
                  {{ formatPercent(container.mem_percent) }}
                </div>
              </TableCell>
              <TableCell class="text-right tabular-nums text-sm">
                <div class="flex items-center justify-end gap-1">
                  <Download class="h-3 w-3 text-muted-foreground" />
                  {{ formatBytes(container.net_input) }}
                </div>
                <div class="flex items-center justify-end gap-1">
                  <Upload class="h-3 w-3 text-muted-foreground" />
                  {{ formatBytes(container.net_output) }}
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
import { ref, onMounted } from 'vue'
import apiClient from '@/lib/api'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { RefreshCw, Download, Upload } from 'lucide-vue-next'

const containers = ref<any[]>([])
const loading = ref(false)

const formatName = (names: string[]) => {
  if (!names || names.length === 0) return 'Unknown'
  return names[0].replace(/^\//, '')
}

const formatBytes = (bytes: number) => {
  if (!bytes || bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const formatCpu = (cpu: number) => {
  if (cpu === null || cpu === undefined) return 'N/A'
  return cpu.toFixed(2) + '%'
}

const formatPercent = (percent: number) => {
  if (percent === null || percent === undefined) return 'N/A'
  return percent.toFixed(1) + '%'
}

const loadContainers = async () => {
  loading.value = true
  try {
    const response = await apiClient.get('/dashboard/containers')
    containers.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Failed to load containers:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadContainers()
  // Auto refresh every 5 seconds
  setInterval(loadContainers, 5000)
})
</script>
