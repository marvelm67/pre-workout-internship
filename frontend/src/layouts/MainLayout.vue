<template>
  <q-layout view="hHh lpR fFf">
    <!-- Header -->
    <q-header elevated class="main-header">
      <q-toolbar class="toolbar-container">
        <!-- Brand -->
        <router-link to="/shop" class="brand-link">
          <q-icon name="visibility" size="28px" color="white" />
          <span class="brand-name">OpticModern</span>
        </router-link>

        <q-space />

        <!-- Desktop Navigation -->
        <div class="nav-links gt-sm">
          <router-link to="/shop" class="nav-link" active-class="nav-link-active">
            <q-icon name="storefront" size="20px" />
            <span>Shop</span>
          </router-link>
          
          <router-link v-if="authStore.isAuthenticated" to="/orders" class="nav-link" active-class="nav-link-active">
            <q-icon name="receipt_long" size="20px" />
            <span>Pesanan</span>
          </router-link>
        </div>

        <!-- Cart Button -->
        <q-btn 
          flat 
          round 
          icon="shopping_cart" 
          color="white"
          @click="goToCart"
          class="cart-btn"
        >
          <q-badge 
            v-if="cartStore.itemCount > 0" 
            color="accent" 
            floating
            class="cart-badge"
          >
            {{ cartStore.itemCount }}
          </q-badge>
        </q-btn>

        <!-- User Menu -->
        <template v-if="authStore.isAuthenticated">
          <q-btn flat round class="user-btn gt-sm">
            <q-avatar size="36px" color="accent" text-color="white">
              {{ authStore.username.charAt(0).toUpperCase() }}
            </q-avatar>
            
            <q-menu anchor="bottom right" self="top right" class="user-menu">
              <q-list style="min-width: 200px">
                <q-item class="user-info">
                  <q-item-section>
                    <q-item-label class="text-weight-bold">{{ authStore.username }}</q-item-label>
                    <q-item-label caption>{{ authStore.user?.email }}</q-item-label>
                    <q-badge 
                      :color="authStore.isAdmin ? 'negative' : 'primary'" 
                      class="q-mt-xs"
                      style="width: fit-content"
                    >
                      {{ authStore.isAdmin ? 'Admin' : 'User' }}
                    </q-badge>
                  </q-item-section>
                </q-item>
                
                <q-separator />
                
                <q-item v-if="authStore.isAdmin" clickable v-close-popup @click="goToAdmin">
                  <q-item-section avatar>
                    <q-icon name="dashboard" />
                  </q-item-section>
                  <q-item-section>Admin Dashboard</q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="router.push('/profile')">
                  <q-item-section avatar>
                    <q-icon name="person" />
                  </q-item-section>
                  <q-item-section>Profil Saya</q-item-section>
                </q-item>
                
                <q-item clickable v-close-popup @click="router.push('/orders')">
                  <q-item-section avatar>
                    <q-icon name="receipt_long" />
                  </q-item-section>
                  <q-item-section>Riwayat Pesanan</q-item-section>
                </q-item>
                
                <q-separator />
                
                <q-item clickable v-close-popup @click="handleLogout" class="text-negative">
                  <q-item-section avatar>
                    <q-icon name="logout" color="negative" />
                  </q-item-section>
                  <q-item-section>Logout</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </template>
        
        <template v-else>
          <q-btn 
            flat 
            no-caps 
            label="Masuk" 
            color="white"
            @click="router.push('/login')"
            class="login-btn gt-sm"
          />
        </template>

        <!-- Mobile Menu Button -->
        <q-btn
          flat
          round
          icon="menu"
          color="white"
          class="lt-md"
          @click="leftDrawerOpen = true"
        />
      </q-toolbar>
    </q-header>

    <!-- Mobile Drawer -->
    <q-drawer
      v-model="leftDrawerOpen"
      side="right"
      overlay
      behavior="mobile"
      class="mobile-drawer"
    >
      <q-list>
        <q-item-label header class="drawer-header">
          <q-icon name="visibility" size="24px" color="primary" />
          <span>OpticModern</span>
        </q-item-label>
        
        <q-separator />
        
        <!-- User Info if logged in -->
        <template v-if="authStore.isAuthenticated">
          <q-item class="user-section">
            <q-item-section avatar>
              <q-avatar color="primary" text-color="white">
                {{ authStore.username.charAt(0).toUpperCase() }}
              </q-avatar>
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ authStore.username }}</q-item-label>
              <q-item-label caption>{{ authStore.user?.email }}</q-item-label>
            </q-item-section>
          </q-item>
          <q-separator />
        </template>
        
        <q-item clickable v-close-popup @click="router.push('/shop')">
          <q-item-section avatar>
            <q-icon name="storefront" />
          </q-item-section>
          <q-item-section>Shop</q-item-section>
        </q-item>
        
        <q-item clickable v-close-popup @click="goToCart">
          <q-item-section avatar>
            <q-icon name="shopping_cart" />
          </q-item-section>
          <q-item-section>
            Keranjang
            <q-badge v-if="cartStore.itemCount > 0" color="accent" class="q-ml-sm">
              {{ cartStore.itemCount }}
            </q-badge>
          </q-item-section>
        </q-item>
        
        <template v-if="authStore.isAuthenticated">
          <q-item clickable v-close-popup @click="router.push('/orders')">
            <q-item-section avatar>
              <q-icon name="receipt_long" />
            </q-item-section>
            <q-item-section>Riwayat Pesanan</q-item-section>
          </q-item>
          
          <q-item clickable v-close-popup @click="router.push('/profile')">
            <q-item-section avatar>
              <q-icon name="person" />
            </q-item-section>
            <q-item-section>Profil Saya</q-item-section>
          </q-item>
          
          <q-item v-if="authStore.isAdmin" clickable v-close-popup @click="goToAdmin">
            <q-item-section avatar>
              <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>Admin Dashboard</q-item-section>
          </q-item>
          
          <q-separator />
          
          <q-item clickable v-close-popup @click="handleLogout" class="text-negative">
            <q-item-section avatar>
              <q-icon name="logout" color="negative" />
            </q-item-section>
            <q-item-section>Logout</q-item-section>
          </q-item>
        </template>
        
        <template v-else>
          <q-separator />
          <q-item clickable v-close-popup @click="router.push('/login')">
            <q-item-section avatar>
              <q-icon name="login" />
            </q-item-section>
            <q-item-section>Masuk</q-item-section>
          </q-item>
          
          <q-item clickable v-close-popup @click="router.push('/register')">
            <q-item-section avatar>
              <q-icon name="person_add" />
            </q-item-section>
            <q-item-section>Daftar</q-item-section>
          </q-item>
        </template>
      </q-list>
    </q-drawer>

    <!-- Page Content -->
    <q-page-container>
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </q-page-container>

    <!-- Footer -->
    <q-footer class="main-footer">
      <div class="footer-content">
        <div class="footer-brand">
          <q-icon name="visibility" size="24px" />
          <span>OpticModern</span>
        </div>
        <p class="footer-text">Premium Eyewear Store - 2024</p>
      </div>
    </q-footer>
  </q-layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()

const leftDrawerOpen = ref(false)

// Fetch cart on mount if authenticated
onMounted(() => {
  if (authStore.isAuthenticated) {
    cartStore.fetchCart()
  }
})

const goToCart = () => {
  if (authStore.isAuthenticated) {
    router.push('/cart')
  } else {
    router.push('/login?redirect=/cart')
  }
}

const goToAdmin = () => {
  router.push('/admin/dashboard')
}

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style lang="scss" scoped>
.main-header {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
}

.toolbar-container {
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
  padding: 0 16px;
}

.brand-link {
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  color: white;
  
  .brand-name {
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: 0.5px;
  }
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: 32px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 20px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.2s;
  font-size: 0.9rem;
  
  &:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
  }
  
  &.nav-link-active {
    background: rgba(233, 69, 96, 0.2);
    color: white;
  }
}

.cart-btn {
  margin-left: 8px;
}

.cart-badge {
  font-size: 0.7rem;
  padding: 2px 5px;
}

.user-btn {
  margin-left: 8px;
}

.user-menu {
  border-radius: 12px;
  overflow: hidden;
}

.user-info {
  background: #f8fafc;
}

.login-btn {
  margin-left: 8px;
}

.mobile-drawer {
  .drawer-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
  }
  
  .user-section {
    background: #f8fafc;
  }
}

.main-footer {
  background: #1a1a2e;
  padding: 24px 16px;
}

.footer-content {
  max-width: 1400px;
  margin: 0 auto;
  text-align: center;
}

.footer-brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  margin-bottom: 8px;
}

.footer-text {
  color: rgba(255, 255, 255, 0.6);
  margin: 0;
  font-size: 0.9rem;
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
