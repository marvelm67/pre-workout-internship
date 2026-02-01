<template>
  <q-page class="admin-orders">
    <div class="orders-container">
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Kelola Pesanan</h1>
          <p class="page-subtitle">Lihat dan update status pesanan</p>
        </div>
      </div>

      <!-- Orders Table -->
      <q-card class="orders-card" flat>
        <q-card-section>
          <!-- Filters -->
          <div class="table-controls">
            <q-input
              v-model="search"
              placeholder="Cari pesanan..."
              outlined
              dense
              class="search-input"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
            
            <q-select
              v-model="filterStatus"
              :options="statusOptions"
              label="Status"
              outlined
              dense
              emit-value
              map-options
              class="filter-select"
            />
          </div>

          <!-- Table -->
          <q-table
            :rows="filteredOrders"
            :columns="columns"
            row-key="id"
            flat
            :loading="isLoading"
            :pagination="{ rowsPerPage: 10 }"
          >
            <template v-slot:body-cell-order_number="props">
              <q-td :props="props">
                <span class="text-weight-bold">{{ props.value || props.row.id }}</span>
              </q-td>
            </template>

            <template v-slot:body-cell-total_amount="props">
              <q-td :props="props">
                <span class="text-weight-bold text-accent">
                  {{ formatPrice(props.value) }}
                </span>
              </q-td>
            </template>

            <template v-slot:body-cell-status="props">
              <q-td :props="props">
                <q-select
                  v-model="props.row.status"
                  :options="statusUpdateOptions"
                  dense
                  outlined
                  emit-value
                  map-options
                  style="min-width: 140px"
                  @update:model-value="(val) => updateOrderStatus(props.row.id, val)"
                >
                  <template v-slot:selected>
                    <q-badge :color="getStatusColor(props.row.status)" text-color="white">
                      {{ getStatusLabel(props.row.status) }}
                    </q-badge>
                  </template>
                </q-select>
              </q-td>
            </template>

            <template v-slot:body-cell-created_at="props">
              <q-td :props="props">
                {{ formatDate(props.value) }}
              </q-td>
            </template>

            <template v-slot:body-cell-actions="props">
              <q-td :props="props">
                <q-btn
                  flat
                  round
                  dense
                  icon="visibility"
                  color="primary"
                  @click="viewOrderDetail(props.row)"
                />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <!-- Order Detail Dialog -->
    <q-dialog v-model="showDetailDialog">
      <q-card class="detail-dialog">
        <q-card-section class="dialog-header">
          <h3>Detail Pesanan</h3>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-card-section v-if="selectedOrder">
          <!-- Order Info -->
          <div class="detail-section">
            <div class="info-row">
              <span class="info-label">No. Pesanan</span>
              <span class="info-value">{{ selectedOrder.order_number || selectedOrder.id }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Tanggal</span>
              <span class="info-value">{{ formatDate(selectedOrder.created_at) }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Status</span>
              <q-badge :color="getStatusColor(selectedOrder.status)" text-color="white">
                {{ getStatusLabel(selectedOrder.status) }}
              </q-badge>
            </div>
          </div>

          <q-separator class="q-my-md" />

          <!-- Customer Info -->
          <div class="detail-section">
            <h4 class="section-title">Informasi Pelanggan</h4>
            <p><strong>Nama:</strong> {{ selectedOrder.username || 'N/A' }}</p>
            <p><strong>Telepon:</strong> {{ selectedOrder.phone }}</p>
            <p><strong>Alamat:</strong> {{ selectedOrder.shipping_address }}</p>
            <p v-if="selectedOrder.notes"><strong>Catatan:</strong> {{ selectedOrder.notes }}</p>
          </div>

          <q-separator class="q-my-md" />

          <!-- Order Items -->
          <div class="detail-section">
            <h4 class="section-title">Produk Dipesan</h4>
            <div class="order-items">
              <div 
                v-for="item in selectedOrder.items" 
                :key="item.id"
                class="order-item"
              >
                <q-img
                  :src="item.image_url || item.product?.image_url"
                  class="item-image"
                  fit="contain"
                />
                <div class="item-info">
                  <span class="item-name">{{ item.name || item.product?.name }}</span>
                  <span class="item-qty">{{ item.quantity }} x {{ formatPrice(item.price) }}</span>
                </div>
                <span class="item-subtotal">{{ formatPrice(item.subtotal || item.price * item.quantity) }}</span>
              </div>
            </div>
          </div>

          <q-separator class="q-my-md" />

          <!-- Total -->
          <div class="total-section">
            <span class="total-label">Total Pembayaran</span>
            <span class="total-value">{{ formatPrice(selectedOrder.total_amount) }}</span>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ordersAPI } from '../../services/api'
import { useCartStore } from '../../stores/cart'
import { Notify } from 'quasar'

const cartStore = useCartStore()

const orders = ref([])
const isLoading = ref(true)
const search = ref('')
const filterStatus = ref(null)
const showDetailDialog = ref(false)
const selectedOrder = ref(null)

const columns = [
  { name: 'order_number', label: 'No. Pesanan', field: 'order_number', align: 'left', sortable: true },
  { name: 'username', label: 'Pelanggan', field: row => row.username || 'N/A', align: 'left' },
  { name: 'total_amount', label: 'Total', field: 'total_amount', align: 'right', sortable: true },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'created_at', label: 'Tanggal', field: 'created_at', align: 'left', sortable: true },
  { name: 'actions', label: 'Aksi', field: 'actions', align: 'center' }
]

const statusOptions = [
  { label: 'Semua', value: null },
  { label: 'Menunggu', value: 'pending' },
  { label: 'Dikonfirmasi', value: 'confirmed' },
  { label: 'Diproses', value: 'processing' },
  { label: 'Dikirim', value: 'shipped' },
  { label: 'Selesai', value: 'delivered' },
  { label: 'Dibatalkan', value: 'cancelled' }
]

const statusUpdateOptions = [
  { label: 'Menunggu', value: 'pending' },
  { label: 'Konfirmasi', value: 'confirmed' },
  { label: 'Proses', value: 'processing' },
  { label: 'Kirim', value: 'shipped' },
  { label: 'Selesai', value: 'delivered' },
  { label: 'Batalkan', value: 'cancelled' }
]

const filteredOrders = computed(() => {
  let result = orders.value
  
  if (filterStatus.value) {
    result = result.filter(o => o.status === filterStatus.value)
  }
  
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    result = result.filter(o => 
      (o.order_number || o.id).toString().toLowerCase().includes(searchLower) ||
      (o.username || '').toLowerCase().includes(searchLower)
    )
  }
  
  return result
})

// Format price
const formatPrice = (price) => {
  return cartStore.formatIDR(price)
}

// Format date
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
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

// Update order status
const updateOrderStatus = async (orderId, newStatus) => {
  try {
    await ordersAPI.updateStatus(orderId, { status: newStatus })
    Notify.create({
      type: 'positive',
      message: 'Status pesanan berhasil diperbarui',
      icon: 'check_circle'
    })
  } catch (err) {
    Notify.create({
      type: 'negative',
      message: 'Gagal mengupdate status pesanan',
      icon: 'error'
    })
    // Revert the change
    fetchOrders()
  }
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
.admin-orders {
  background: #f8fafc;
  min-height: 100vh;
  padding: 24px;
}

.orders-container {
  max-width: 1400px;
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

.orders-card {
  border-radius: 16px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.table-controls {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}

.search-input {
  flex: 1;
  max-width: 300px;
}

.filter-select {
  width: 180px;
}

.detail-dialog {
  width: 100%;
  max-width: 550px;
  border-radius: 16px;
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  
  h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a1a2e;
  }
}

.detail-section {
  .section-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 12px;
  }
  
  p {
    margin: 8px 0;
    color: #4b5563;
  }
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
}

.info-label {
  color: #6b7280;
}

.info-value {
  font-weight: 600;
  color: #1a1a2e;
}

.order-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f8fafc;
  border-radius: 12px;
}

.item-image {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  background: white;
}

.item-info {
  flex: 1;
}

.item-name {
  display: block;
  font-weight: 600;
  color: #1a1a2e;
  font-size: 0.9rem;
}

.item-qty {
  font-size: 0.8rem;
  color: #6b7280;
}

.item-subtotal {
  font-weight: 700;
  color: #1a1a2e;
}

.total-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  background: #1a1a2e;
  border-radius: 12px;
}

.total-label {
  font-weight: 600;
  color: white;
}

.total-value {
  font-size: 1.25rem;
  font-weight: 800;
  color: #e94560;
}

// Responsive
@media (max-width: 767px) {
  .admin-orders {
    padding: 16px;
  }
  
  .table-controls {
    flex-direction: column;
  }
  
  .search-input,
  .filter-select {
    width: 100%;
    max-width: none;
  }
}
</style>
