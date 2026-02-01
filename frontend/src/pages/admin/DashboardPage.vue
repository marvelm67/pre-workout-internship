<template>
  <q-page class="admin-dashboard">
    <div class="dashboard-container">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Selamat datang, {{ authStore.username }}!</p>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <q-card class="stat-card" flat>
          <q-card-section>
            <div class="stat-content">
              <div class="stat-icon bg-primary">
                <q-icon name="shopping_bag" color="white" size="28px" />
              </div>
              <div class="stat-info">
                <span class="stat-value">{{ stats.totalOrders || 0 }}</span>
                <span class="stat-label">Total Pesanan</span>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <q-card class="stat-card" flat>
          <q-card-section>
            <div class="stat-content">
              <div class="stat-icon bg-positive">
                <q-icon name="attach_money" color="white" size="28px" />
              </div>
              <div class="stat-info">
                <span class="stat-value">{{ formatPrice(stats.totalRevenue || 0) }}</span>
                <span class="stat-label">Total Pendapatan</span>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <q-card class="stat-card" flat>
          <q-card-section>
            <div class="stat-content">
              <div class="stat-icon bg-info">
                <q-icon name="inventory_2" color="white" size="28px" />
              </div>
              <div class="stat-info">
                <span class="stat-value">{{ stats.totalProducts || 0 }}</span>
                <span class="stat-label">Total Produk</span>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <q-card class="stat-card" flat>
          <q-card-section>
            <div class="stat-content">
              <div class="stat-icon bg-warning">
                <q-icon name="people" color="white" size="28px" />
              </div>
              <div class="stat-info">
                <span class="stat-value">{{ stats.totalUsers || 0 }}</span>
                <span class="stat-label">Total Pengguna</span>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Charts Row -->
      <div class="charts-row">
        <!-- Order Status Chart -->
        <q-card class="chart-card" flat>
          <q-card-section>
            <h3 class="card-title">Status Pesanan</h3>
            <div class="status-list">
              <div 
                v-for="(count, status) in stats.ordersByStatus" 
                :key="status"
                class="status-item"
              >
                <div class="status-info">
                  <q-badge :color="getStatusColor(status)" class="status-badge" />
                  <span class="status-label">{{ getStatusLabel(status) }}</span>
                </div>
                <span class="status-count">{{ count }}</span>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Quick Actions -->
        <q-card class="chart-card" flat>
          <q-card-section>
            <h3 class="card-title">Aksi Cepat</h3>
            <div class="quick-actions">
              <q-btn
                outline
                no-caps
                color="primary"
                label="Tambah Produk"
                icon="add"
                class="action-btn"
                @click="router.push('/admin/products')"
              />
              <q-btn
                outline
                no-caps
                color="info"
                label="Lihat Pesanan"
                icon="receipt_long"
                class="action-btn"
                @click="router.push('/admin/orders')"
              />
              <q-btn
                outline
                no-caps
                color="warning"
                label="Kelola User"
                icon="people"
                class="action-btn"
                @click="router.push('/admin/users')"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Recent Orders -->
      <q-card class="recent-card" flat>
        <q-card-section>
          <div class="card-header">
            <h3 class="card-title">Pesanan Terbaru</h3>
            <q-btn
              flat
              no-caps
              color="primary"
              label="Lihat Semua"
              @click="router.push('/admin/orders')"
            />
          </div>
          
          <q-table
            :rows="recentOrders"
            :columns="orderColumns"
            row-key="id"
            flat
            :loading="isLoading"
            hide-pagination
            :rows-per-page-options="[5]"
          >
            <template v-slot:body-cell-status="props">
              <q-td :props="props">
                <q-badge 
                  :color="getStatusColor(props.row.status)"
                  text-color="white"
                >
                  {{ getStatusLabel(props.row.status) }}
                </q-badge>
              </q-td>
            </template>

            <template v-slot:body-cell-total_amount="props">
              <q-td :props="props">
                <span class="text-weight-bold">{{ formatPrice(props.value) }}</span>
              </q-td>
            </template>

            <template v-slot:body-cell-created_at="props">
              <q-td :props="props">
                {{ formatDate(props.value) }}
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { adminAPI, ordersAPI } from '../../services/api'
import { useCartStore } from '../../stores/cart'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()

const isLoading = ref(true)
const stats = ref({
  totalOrders: 0,
  totalRevenue: 0,
  totalProducts: 0,
  totalUsers: 0,
  ordersByStatus: {}
})
const recentOrders = ref([])

const orderColumns = [
  { name: 'order_number', label: 'No. Pesanan', field: row => row.order_number || row.id, align: 'left' },
  { name: 'username', label: 'Pelanggan', field: row => row.username || 'N/A', align: 'left' },
  { name: 'total_amount', label: 'Total', field: 'total_amount', align: 'right' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'created_at', label: 'Tanggal', field: 'created_at', align: 'left' }
]

// Format price
const formatPrice = (price) => {
  return cartStore.formatIDR(price)
}

// Format date
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

// Get status color
const getStatusColor = (status) => {
  const colors = {
    pending: 'warning',
    confirmed: 'info',
    processing: 'primary',
    shipped: 'purple',
    delivered: 'positive',
    cancelled: 'negative'
  }
  return colors[status] || 'grey'
}

// Get status label
const getStatusLabel = (status) => {
  const labels = {
    pending: 'Menunggu',
    confirmed: 'Dikonfirmasi',
    processing: 'Diproses',
    shipped: 'Dikirim',
    delivered: 'Selesai',
    cancelled: 'Dibatalkan'
  }
  return labels[status] || status
}

// Fetch dashboard data
const fetchDashboardData = async () => {
  isLoading.value = true
  
  try {
    // Try to fetch from API
    const [dashboardRes, ordersRes] = await Promise.all([
      adminAPI.getDashboardStats().catch(() => null),
      ordersAPI.getAll().catch(() => null)
    ])
    
    if (dashboardRes?.data?.data) {
      stats.value = dashboardRes.data.data
    } else {
      // Mock data for demo
      stats.value = {
        totalOrders: 24,
        totalRevenue: 15750000,
        totalProducts: 7,
        totalUsers: 12,
        ordersByStatus: {
          pending: 5,
          confirmed: 3,
          processing: 4,
          shipped: 6,
          delivered: 4,
          cancelled: 2
        }
      }
    }
    
    if (ordersRes?.data?.data) {
      recentOrders.value = ordersRes.data.data.slice(0, 5)
    } else {
      // Mock data
      recentOrders.value = []
    }
  } catch (err) {
    console.error('Failed to fetch dashboard data:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>

<style lang="scss" scoped>
.admin-dashboard {
  background: #f8fafc;
  min-height: 100vh;
  padding: 24px;
}

.dashboard-container {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 32px;
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  border-radius: 16px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1a1a2e;
}

.stat-label {
  font-size: 0.85rem;
  color: #6b7280;
}

.charts-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 24px;
}

.chart-card {
  border-radius: 16px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.card-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 16px;
}

.status-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f3f4f6;
  
  &:last-child {
    border-bottom: none;
  }
}

.status-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.status-badge {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  padding: 0;
}

.status-label {
  color: #4b5563;
}

.status-count {
  font-weight: 700;
  color: #1a1a2e;
}

.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.action-btn {
  justify-content: flex-start;
  padding: 12px 16px;
}

.recent-card {
  border-radius: 16px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

// Responsive
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 767px) {
  .admin-dashboard {
    padding: 16px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .charts-row {
    grid-template-columns: 1fr;
  }
  
  .stat-value {
    font-size: 1.25rem;
  }
}
</style>
