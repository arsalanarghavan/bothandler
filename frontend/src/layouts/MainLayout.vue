<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <header class="flex h-16 shrink-0 items-center gap-2 border-b px-4">
        <SidebarTrigger class="-ml-1" />
        <Separator orientation="vertical" class="mr-2 h-4" />
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem class="hidden md:block">
              <BreadcrumbLink :href="breadcrumbs[0]?.href || '/'">
                {{ breadcrumbs[0]?.title || 'Home' }}
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator v-if="breadcrumbs.length > 1" class="hidden md:block" />
            <BreadcrumbItem v-if="breadcrumbs.length > 1">
              <BreadcrumbPage>{{ breadcrumbs[1]?.title }}</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>
        <div class="ml-auto flex items-center gap-2">
          <ThemeToggle />
        </div>
      </header>
      <main class="flex flex-1 flex-col gap-4 p-4 lg:gap-6 lg:p-6">
        <slot />
      </main>
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppSidebar from '@/components/AppSidebar.vue'
import ThemeToggle from '@/components/ThemeToggle.vue'
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from '@/components/ui/sidebar'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { Separator } from '@/components/ui/separator'

const route = useRoute()

const breadcrumbs = computed(() => {
  const path = route.path
  if (path === '/') return [{ title: 'Dashboard', href: '/' }]
  if (path === '/bots') return [{ title: 'Dashboard', href: '/' }, { title: 'Services' }]
  if (path.startsWith('/bots/')) return [{ title: 'Dashboard', href: '/' }, { title: 'Service Details' }]
  if (path === '/deploy-bot') return [{ title: 'Dashboard', href: '/' }, { title: 'Deploy Service' }]
  if (path === '/services') return [{ title: 'Dashboard', href: '/' }, { title: 'Containers' }]
  if (path === '/settings') return [{ title: 'Dashboard', href: '/' }, { title: 'Settings' }]
  return [{ title: 'Dashboard', href: '/' }]
})
</script>
