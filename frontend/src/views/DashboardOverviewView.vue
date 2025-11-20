<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
      <p class="text-muted-foreground">Monitor your server and container statistics</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Containers</CardTitle>
          <Server class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ summary.total || 0 }}</div>
          <p class="text-xs text-muted-foreground">All containers on server</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Running</CardTitle>
          <Activity class="h-4 w-4 text-green-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ summary.running || 0 }}</div>
          <p class="text-xs text-muted-foreground">Active containers</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Stopped</CardTitle>
          <AlertCircle class="h-4 w-4 text-orange-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-orange-600">{{ summary.stopped || 0 }}</div>
          <p class="text-xs text-muted-foreground">Inactive containers</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Avg CPU Usage</CardTitle>
          <Cpu class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ avgCpu }}%</div>
          <p class="text-xs text-muted-foreground">Average across containers</p>
        </CardContent>
      </Card>
    </div>

    <!-- Resource Usage Cards -->
    <div class="grid gap-4 md:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle>Memory Usage</CardTitle>
          <CardDescription>Total memory consumption</CardDescription>
        </CardHeader>
        <CardContent class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">Used</span>
            <span class="text-sm font-medium">{{ formatBytes(summary.total_mem_usage || 0) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">Total</span>
            <span class="text-sm font-medium">{{ formatBytes(summary.total_mem_limit || 0) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">Percentage</span>
            <span class="text-sm font-medium">{{ memPercent }}%</span>
          </div>
          <div class="w-full bg-secondary rounded-full h-2">
            <div 
              class="bg-primary h-2 rounded-full transition-all"
              :style="{ width: memPercent + '%' }"
            ></div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Network I/O</CardTitle>
          <CardDescription>Total network traffic</CardDescription>
        </CardHeader>
        <CardContent class="space-y-2">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Download class="h-4 w-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">Input</span>
            </div>
            <span class="text-sm font-medium">{{ formatBytes(summary.total_net_input || 0) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <Upload class="h-4 w-4 text-muted-foreground" />
              <span class="text-sm text-muted-foreground">Output</span>
            </div>
            <span class="text-sm font-medium">{{ formatBytes(summary.total_net_output || 0) }}</span>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !summary.total" class="flex items-center justify-center p-12">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
        <p class="text-muted-foreground">Loading dashboard data...</p>
      </div>
    </div>

    <!-- Error State -->
    <Card v-if="error" class="border-destructive">
      <CardContent class="pt-6">
        <div class="flex items-start gap-4">
          <AlertCircle class="h-5 w-5 text-destructive mt-0.5" />
          <div>
            <h3 class="font-semibold text-destructive">Error loading dashboard</h3>
            <p class="text-sm text-muted-foreground mt-1">{{ error }}</p>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Server, Activity, AlertCircle, Cpu, Download, Upload } from 'lucide-vue-next'

const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api'

const summary = ref<any>({})
const loading = ref(false)
const error = ref('')

const avgCpu = computed(() => {
  const cpu = summary.value.avg_cpu_percent
  return cpu ? cpu.toFixed(2) : '0.00'
})

const memPercent = computed(() => {
  const percent = summary.value.total_mem_percent
  return percent ? percent.toFixed(2) : '0.00'
})

const formatBytes = (bytes: number) => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

const loadData = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get(`${API_BASE}/dashboard/summary`)
    summary.value = response.data
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to load dashboard data'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadData()
  // Refresh every 10 seconds
  setInterval(loadData, 10000)
})
</script>
