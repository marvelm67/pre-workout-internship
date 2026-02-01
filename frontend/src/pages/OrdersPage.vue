<template>
  <q-page class="orders-page">
    <div class="orders-container">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Riwayat Pesanan</h1>
        <p class="page-subtitle">Lihat status dan detail pesanan Anda</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="loading-state">
        <q-spinner-dots size="50px" color="primary" />
        <p>Memuat pesanan...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="orders.length === 0" class="empty-state">
        <q-icon name="receipt_long" size="80px" color="grey-4" />
        <h3>Belum Ada Pesanan</h3>
        <p>Anda belum memiliki riwayat pesanan</p>
        <q-btn
          color="primary"
          label="Mulai Belanja"
          no-caps
          unelevated
          rounded
          @click="router.push('/shop')"
          class="q-mt-md"
        />
      </div>

      <!-- Orders List -->
      <div v-else class="orders-list">
        <q-card 
          v-for="order in orders" 
          :key="order.id"
          class="order-card"
          flat
        >
          <!-- Order Header -->
          <div class="order-header">
            <div class="order-info">
              <span class="order-number">{{ order.order_number || order.id }}</span>
              <span class="order-date">{{ formatDate(order.created_at) }}</span>
            </div>
            <q-badge 
              :color="getStatusColor(order.status)"
              text-color="white"
              class="status-badge"
            >
              {{ getStatusLabel(order.status) }}
            </q-badge>
          </div>

          <q-separator />

          <!-- Order Items -->
          <div class="order-items">
            <div 
              v-for="item in order.items?.slice(0, 2)" 
              :key="item.id"
              class="order-item"
            >
              <q-img
                :src="item.image_url || item.product?.image_url"
                class="item-image"
                fit="contain"
              >
                <template v-slot:error>
                  <div class="absolute-full flex flex-center bg-grey-2">
                    <q-icon name="broken_image" size="20px" color="grey-5" />
                  </div>
                </template>
              </q-img>
              <div class="item-details">
                <span class="item-name">{{ item.name || item.product?.name }}</span>
                <span class="item-qty">{{ item.quantity }} x {{ formatPrice(item.price) }}</span>
              </div>
            </div>
            
            <div v-if="order.items?.length > 2" class="more-items">
              +{{ order.items.length - 2 }} produk lainnya
            </div>
          </div>

          <q-separator />

          <!-- Order Footer -->
          <div class="order-footer">
            <div class="order-total">
              <span class="total-label">Total Pesanan:</span>
              <span class="total-value">{{ formatPrice(order.total_amount) }}</span>
            </div>
            <q-btn
              flat
              no-caps
              color="primary"
              label="Lihat Detail"
              icon-right="chevron_right"
              @click="viewOrderDetail(order)"
            />
          </div>
        </q-card>
      </div>
    </div>

    <!-- Order Detail Dialog -->
    <q-dialog v-model="showDetailDialog" maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card class="detail-dialog">
        <q-toolbar class="detail-toolbar">
          <q-btn flat round dense icon="close" v-close-popup />
          <q-toolbar-title>Detail Pesanan</q-toolbar-title>
        </q-toolbar>

        <q-card-section v-if="selectedOrder" class="detail-content">
          <!-- Order Status -->
          <div class="detail-section">
            <div class="status-timeline">
              <q-badge 
                :color="getStatusColor(selectedOrder.status)"
                text-color="white"
                class="status-badge-large"
              >
                {{ getStatusLabel(selectedOrder.status) }}
              </q-badge>
              <p class="order-number-detail">{{ selectedOrder.order_number || selectedOrder.id }}</p>
              <p class="order-date-detail">{{ formatDate(selectedOrder.created_at) }}</p>
            </div>
          </div>

          <!-- Shipping Info -->
          <div class="detail-section">
            <h4 class="section-title">
              <q-icon name="local_shipping" class="q-mr-sm" />
              Informasi Pengiriman
            </h4>
            <p class="info-text"><strong>Telepon:</strong> {{ selectedOrder.phone }}</p>
            <p class="info-text"><strong>Alamat:</strong> {{ selectedOrder.shipping_address }}</p>
            <p v-if="selectedOrder.notes" class="info-text"><strong>Catatan:</strong> {{ selectedOrder.notes }}</p>
          </div>

          <!-- Order Items -->
          <div class="detail-section">
            <h4 class="section-title">
              <q-icon name="shopping_bag" class="q-mr-sm" />
              Produk Dipesan
            </h4>
            <div class="detail-items">
              <div 
                v-for="item in selectedOrder.items" 
                :key="item.id"
                class="detail-item"
              >
                <q-img
                  :src="item.image_url || item.product?.image_url"
                  class="detail-item-image"
                  fit="contain"
                />
                <div class="detail-item-info">
                  <span class="detail-item-name">{{ item.name || item.product?.name }}</span>
                  <span class="detail-item-qty">{{ item.quantity }} x {{ formatPrice(item.price) }}</span>
                </div>
                <span class="detail-item-subtotal">{{ formatPrice(item.subtotal || item.price * item.quantity) }}</span>
              </div>
            </div>
          </div>

          <!-- Total -->
          <div class="detail-total">
            <span>Total Pembayaran</span>
            <span class="total-amount">{{ formatPrice(selectedOrder.total_amount) }}</span>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ordersAPI } from '../services/api'
import { useCartStore } from '../stores/cart'

const router = useRouter()
const cartStore = useCartStore()

const orders = ref([])
const isLoading = ref(true)
const showDetailDialog = ref(false)
const selectedOrder = ref(null)

// Format price
const formatPrice = (price) => {
  return cartStore.formatIDR(price)
}

// Format date
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
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

// View order detail
const viewOrderDetail = (order) => {
  selectedOrder.value = order
  showDetailDialog.value = true
}

// Fetch orders
const fetchOrders = async () => {
  isLoading.value = true
  try {
    const response = await ordersAPI.getAll()
    if (response.data.status === 200) {
      orders.value = response.data.data || []
    }
  } catch (err) {
    console.error('Failed to fetch orders:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchOrders()
})
</script>

<style lang="scss" scoped>
.orders-page {
  background: #f8fafc;
  min-height: 100vh;
  padding: 24px 16px;
}

.orders-container {
  max-width: 800px;
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

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  text-align: center;
  
  h3 {
    margin: 24px 0 8px;
    color: #374151;
  }
  
  p {
    color: #6b7280;
    margin: 0;
  }
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.order-card {
  border-radius: 16px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
}

.order-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.order-number {
  font-weight: 700;
  color: #1a1a2e;
  font-size: 0.95rem;
}

.order-date {
  font-size: 0.85rem;
  color: #6b7280;
}

.status-badge {
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 20px;
}

.order-items {
  padding: 16px;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  
  &:last-child {
    margin-bottom: 0;
  }
}

.item-image {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  background: #f8fafc;
}

.item-details {
  flex: 1;
}

.item-name {
  display: block;
  font-weight: 600;
  color: #1a1a2e;
  font-size: 0.9rem;
}

.item-qty {
  font-size: 0.85rem;
  color: #6b7280;
}

.more-items {
  text-align: center;
  color: #6b7280;
  font-size: 0.9rem;
  padding-top: 8px;
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #f8fafc;
}

.order-total {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.total-label {
  font-size: 0.85rem;
  color: #6b7280;
}

.total-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #e94560;
}

// Detail Dialog
.detail-dialog {
  max-width: 600px;
  margin: auto;
}

.detail-toolbar {
  background: #1a1a2e;
  color: white;
}

.detail-content {
  padding: 24px;
}

.detail-section {
  margin-bottom: 24px;
}

.status-timeline {
  text-align: center;
  padding: 20px;
  background: #f8fafc;
  border-radius: 12px;
}

.status-badge-large {
  font-size: 1rem;
  padding: 8px 20px;
  border-radius: 20px;
}

.order-number-detail {
  font-weight: 700;
  color: #1a1a2e;
  margin: 12px 0 4px;
}

.order-date-detail {
  color: #6b7280;
  margin: 0;
}

.section-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 12px;
  display: flex;
  align-items: center;
}

.info-text {
  color: #4b5563;
  margin: 8px 0;
  font-size: 0.95rem;
}

.detail-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f8fafc;
  border-radius: 12px;
}

.detail-item-image {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  background: white;
}

.detail-item-info {
  flex: 1;
}

.detail-item-name {
  display: block;
  font-weight: 600;
  color: #1a1a2e;
}

.detail-item-qty {
  font-size: 0.85rem;
  color: #6b7280;
}

.detail-item-subtotal {
  font-weight: 700;
  color: #1a1a2e;
}

.detail-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #1a1a2e;
  border-radius: 12px;
  color: white;
  font-weight: 700;
}

.total-amount {
  font-size: 1.25rem;
  color: #e94560;
}

// Responsive
@media (max-width: 599px) {
  .page-title {
    font-size: 1.5rem;
  }
  
  .order-header {
    flex-wrap: wrap;
    gap: 12px;
  }
}
</style>
