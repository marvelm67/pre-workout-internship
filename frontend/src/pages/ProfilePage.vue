<template>
  <q-page class="profile-page">
    <div class="profile-container">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Profil Saya</h1>
        <p class="page-subtitle">Kelola informasi akun Anda</p>
      </div>

      <!-- Profile Card -->
      <q-card class="profile-card" flat>
        <q-card-section>
          <div class="profile-header">
            <q-avatar size="100px" color="primary" text-color="white" class="profile-avatar">
              {{ authStore.username.charAt(0).toUpperCase() }}
            </q-avatar>
            <div class="profile-info">
              <h2 class="profile-name">{{ authStore.username }}</h2>
              <p class="profile-email">{{ authStore.user?.email }}</p>
              <q-badge 
                :color="authStore.isAdmin ? 'negative' : 'primary'"
                class="role-badge"
              >
                {{ authStore.isAdmin ? 'Admin' : 'User' }}
              </q-badge>
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-section>
          <h3 class="section-title">Informasi Akun</h3>
          
          <div class="info-list">
            <div class="info-item">
              <q-icon name="person" color="primary" size="24px" />
              <div class="info-content">
                <span class="info-label">Username</span>
                <span class="info-value">{{ authStore.username }}</span>
              </div>
            </div>
            
            <div class="info-item">
              <q-icon name="email" color="primary" size="24px" />
              <div class="info-content">
                <span class="info-label">Email</span>
                <span class="info-value">{{ authStore.user?.email }}</span>
              </div>
            </div>
            
            <div class="info-item">
              <q-icon name="badge" color="primary" size="24px" />
              <div class="info-content">
                <span class="info-label">Role</span>
                <span class="info-value">{{ authStore.isAdmin ? 'Administrator' : 'Pengguna' }}</span>
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Quick Links -->
      <q-card class="quick-links-card" flat>
        <q-card-section>
          <h3 class="section-title">Menu Cepat</h3>
          
          <q-list>
            <q-item clickable v-ripple @click="router.push('/orders')">
              <q-item-section avatar>
                <q-icon name="receipt_long" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Riwayat Pesanan</q-item-label>
                <q-item-label caption>Lihat semua pesanan Anda</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" color="grey" />
              </q-item-section>
            </q-item>
            
            <q-item clickable v-ripple @click="router.push('/cart')">
              <q-item-section avatar>
                <q-icon name="shopping_cart" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Keranjang Belanja</q-item-label>
                <q-item-label caption>{{ cartStore.itemCount }} item di keranjang</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" color="grey" />
              </q-item-section>
            </q-item>
            
            <q-item v-if="authStore.isAdmin" clickable v-ripple @click="router.push('/admin/dashboard')">
              <q-item-section avatar>
                <q-icon name="dashboard" color="negative" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Admin Dashboard</q-item-label>
                <q-item-label caption>Kelola toko Anda</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" color="grey" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>

      <!-- Logout Button -->
      <q-btn
        color="negative"
        label="Logout"
        icon="logout"
        no-caps
        outline
        class="logout-btn"
        @click="handleLogout"
      />
    </div>
  </q-page>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style lang="scss" scoped>
.profile-page {
  background: #f8fafc;
  min-height: 100vh;
  padding: 24px 16px;
}

.profile-container {
  max-width: 600px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 24px;
}

.page-title {
  font-size: 2rem;
  font-weight: 800;
  color: #1a1a2e;
  margin: 0 0 8px;
}

.page-subtitle {
  color: #6b7280;
  margin: 0;
}

.profile-card,
.quick-links-card {
  border-radius: 16px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  margin-bottom: 16px;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 24px;
}

.profile-avatar {
  font-size: 2.5rem;
  font-weight: 700;
}

.profile-info {
  flex: 1;
}

.profile-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 4px;
}

.profile-email {
  color: #6b7280;
  margin: 0 0 8px;
}

.role-badge {
  font-weight: 600;
  padding: 4px 12px;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 16px;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px;
  background: #f8fafc;
  border-radius: 12px;
}

.info-content {
  display: flex;
  flex-direction: column;
}

.info-label {
  font-size: 0.85rem;
  color: #6b7280;
}

.info-value {
  font-weight: 600;
  color: #1a1a2e;
}

.logout-btn {
  width: 100%;
  margin-top: 16px;
  padding: 12px;
}

// Responsive
@media (max-width: 599px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
}
</style>
