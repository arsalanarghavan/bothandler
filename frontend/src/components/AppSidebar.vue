<template>
  <Sidebar collapsible="icon">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <RouterLink to="/">
              <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                <Server class="size-4" />
              </div>
              <div class="grid flex-1 text-left text-sm leading-tight">
                <span class="truncate font-semibold">Bot Hosting</span>
                <span class="truncate text-xs">Dashboard</span>
              </div>
            </RouterLink>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>
    
    <SidebarContent>
      <SidebarGroup>
        <SidebarGroupLabel>Main</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem v-for="item in menuItems" :key="item.path">
            <SidebarMenuButton as-child :is-active="$route.path === item.path">
              <RouterLink :to="item.path">
                <component :is="item.icon" class="size-4" />
                <span>{{ item.title }}</span>
              </RouterLink>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>
    </SidebarContent>
    
    <SidebarFooter>
      <SidebarMenu>
        <SidebarMenuItem>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <SidebarMenuButton size="lg">
                <Avatar class="size-8">
                  <AvatarFallback>{{ userInitials }}</AvatarFallback>
                </Avatar>
                <div class="grid flex-1 text-left text-sm leading-tight">
                  <span class="truncate font-semibold">{{ userName }}</span>
                  <span class="truncate text-xs">{{ userEmail }}</span>
                </div>
                <ChevronsUpDown class="ml-auto size-4" />
              </SidebarMenuButton>
            </DropdownMenuTrigger>
            <DropdownMenuContent side="top" align="end" class="w-[--radix-dropdown-menu-trigger-width]">
              <DropdownMenuItem @click="handleLogout">
                <LogOut class="mr-2 size-4" />
                Logout
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarFooter>
  </Sidebar>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Server, LayoutDashboard, Box, PlusCircle, Settings, LogOut, ChevronsUpDown } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const menuItems = [
  { title: 'Dashboard', path: '/', icon: LayoutDashboard },
  { title: 'Services', path: '/bots', icon: Box },
  { title: 'Deploy Service', path: '/deploy-bot', icon: PlusCircle },
  { title: 'Containers', path: '/services', icon: Server },
  { title: 'Settings', path: '/settings', icon: Settings },
]

const userName = computed(() => authStore.user?.name || 'Admin')
const userEmail = computed(() => authStore.user?.email || 'admin@example.com')
const userInitials = computed(() => {
  const name = userName.value
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

