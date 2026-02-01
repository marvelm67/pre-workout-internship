<template>
  <q-layout view="lHh Lpr lFf">
    <!-- Sidebar -->
    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      class="admin-drawer"
      :width="260"
    >
      <q-list>
        <!-- Brand -->
        <q-item class="drawer-header">
          <q-item-section avatar>
            <q-icon name="visibility" size="28px" color="primary" />
          </q-item-section>
          <q-item-section>
            <q-item-label class="brand-text">OpticModern</q-item-label>
            <q-item-label caption class="text-accent">Admin Panel</q-item-label>
          </q-item-section>
        </q-item>

        <q-separator />

        <!-- Navigation -->
        <q-item
          clickable
          v-ripple
          :to="{ name: 'admin-dashboard' }"
          active-class="nav-active"
        >
          <q-item-section avatar>
            <q-icon name="dashboard" />
          </q-item-section>
          <q-item-section>Dashboard</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          :to="{ name: 'admin-products' }"
          active-class="nav-active"
        >
          <q-item-section avatar>
            <q-icon name="inventory_2" />
          </q-item-section>
          <q-item-section>Produk</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          :to="{ name: 'admin-orders' }"
          active-class="nav-active"
        >
          <q-item-section avatar>
            <q-icon name="receipt_long" />
          </q-item-section>
          <q-item-section>Pesanan</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          :to="{ name: 'admin-users' }"
          active-class="nav-active"
        >
          <q-item-section avatar>
            <q-icon name="people" />
          </q-item-section>
          <q-item-section>Pengguna</q-item-section>
        </q-item>

        <q-separator class="q-my-md" />

        <!-- Back to Shop -->
        <q-item
          clickable
          v-ripple
          @click="router.push('/shop')"
        >
          <q-item-section avatar>
            <q-icon name="storefront" />
          </q-item-section>
          <q-item-section>Kembali ke Shop</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          @click="handleLogout"
          class="text-negative"
        >
          <q-item-section avatar>
            <q-icon name="logout" color="negative" />
          </q-item-section>
          <q-item-section>Logout</q-item-section>
        </q-item>
      </q-list>

      <!-- User Info at Bottom -->
      <div class="absolute-bottom user-section">
        <q-separator />
        <q-item>
          <q-item-section avatar>
            <q-avatar color="primary" text-color="white" size="40px">
              {{ authStore.username.charAt(0).toUpperCase() }}
            </q-avatar>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-weight-bold">{{ authStore.username }}</q-item-label>
            <q-item-label caption>{{ authStore.user?.email }}</q-item-label>
          </q-item-section>
        </q-item>
      </div>
    </q-drawer>

    <!-- Header -->
    <q-header elevated class="admin-header">
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="toggleLeftDrawer"
          class="lt-md"
        />

        <q-toolbar-title class="gt-sm">
          {{ pageTitle }}
        </q-toolbar-title>

        <q-space />

        <!-- Notifications -->
        <q-btn flat round icon="notifications">
          <q-badge color="accent" floating>3</q-badge>
        </q-btn>

        <!-- User Menu (Mobile) -->
        <q-btn flat round class="lt-md">
          <q-avatar size="32px" color="accent" text-color="white">
            {{ authStore.username.charAt(0).toUpperCase() }}
          </q-avatar>
          <q-menu>
            <q-list style="min-width: 180px">
              <q-item clickable v-close-popup @click="router.push('/shop')">
                <q-item-section avatar>
                  <q-icon name="storefront" />
                </q-item-section>
                <q-item-section>Ke Shop</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="handleLogout" class="text-negative">
                <q-item-section avatar>
                  <q-icon name="logout" color="negative" />
                </q-item-section>
                <q-item-section>Logout</q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </q-toolbar>
    </q-header>

    <!-- Page Content -->
    <q-page-container>
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const leftDrawerOpen = ref(false)

const pageTitle = computed(() => {
  return route.meta.title || 'Admin Dashboard'
})

const toggleLeftDrawer = () => {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style lang="scss" scoped>
.admin-drawer {
  background: white;
}

.drawer-header {
  padding: 20px 16px;
  
  .brand-text {
    font-size: 1.25rem;
    font-weight: 800;
    color: #1a1a2e;
  }
}

.nav-active {
  background: linear-gradient(90deg, rgba(26, 26, 46, 0.1) 0%, transparent 100%);
  border-left: 3px solid #1a1a2e;
  
  .q-icon {
    color: #1a1a2e;
  }
}

.user-section {
  padding: 8px 0;
  background: #f8fafc;
}

.admin-header {
  background: #1a1a2e;
}

// Transitions
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
