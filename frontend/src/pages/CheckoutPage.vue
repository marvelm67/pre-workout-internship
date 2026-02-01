<template>
  <q-page class="checkout-page">
    <div class="checkout-container">
      <!-- Page Header -->
      <div class="page-header">
        <q-btn
          flat
          round
          icon="arrow_back"
          color="grey-7"
          @click="router.push('/cart')"
        />
        <div>
          <h1 class="page-title">Checkout</h1>
          <p class="page-subtitle">Lengkapi informasi pengiriman</p>
        </div>
      </div>

      <!-- Empty Cart Redirect -->
      <div v-if="cartStore.isEmpty" class="empty-state">
        <q-icon name="shopping_cart" size="80px" color="grey-4" />
        <h3>Keranjang Kosong</h3>
        <p>Tambahkan produk ke keranjang terlebih dahulu</p>
        <q-btn
          color="primary"
          label="Belanja Sekarang"
          no-caps
          unelevated
          rounded
          @click="router.push('/shop')"
          class="q-mt-md"
        />
      </div>

      <!-- Checkout Form -->
      <div v-else class="checkout-content">
        <!-- Shipping Form -->
        <div class="shipping-form">
          <q-card class="form-card" flat>
            <q-card-section>
              <h3 class="section-title">
                <q-icon name="local_shipping" class="q-mr-sm" />
                Informasi Pengiriman
              </h3>
              
              <q-form @submit.prevent="handleCheckout" class="q-gutter-md">
                <!-- Phone -->
                <q-input
                  v-model="form.phone"
                  label="Nomor Telepon"
                  outlined
                  :rules="[val => !!val || 'Nomor telepon wajib diisi']"
                >
                  <template v-slot:prepend>
                    <q-icon name="phone" />
                  </template>
                </q-input>

                <!-- Address -->
                <q-input
                  v-model="form.shipping_address"
                  label="Alamat Lengkap"
                  type="textarea"
                  outlined
                  rows="3"
                  :rules="[val => !!val || 'Alamat wajib diisi']"
                >
                  <template v-slot:prepend>
                    <q-icon name="location_on" />
                  </template>
                </q-input>

                <!-- Notes -->
                <q-input
                  v-model="form.notes"
                  label="Catatan (Opsional)"
                  type="textarea"
                  outlined
                  rows="2"
                  hint="Catatan untuk penjual atau kurir"
                >
                  <template v-slot:prepend>
                    <q-icon name="note" />
                  </template>
                </q-input>
              </q-form>
            </q-card-section>
          </q-card>

          <!-- Payment Method -->
          <q-card class="form-card q-mt-md" flat>
            <q-card-section>
              <h3 class="section-title">
                <q-icon name="payment" class="q-mr-sm" />
                Metode Pembayaran
              </h3>
              
              <q-list>
                <q-item tag="label" class="payment-option">
                  <q-item-section avatar>
                    <q-radio v-model="paymentMethod" val="cod" color="primary" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Cash on Delivery (COD)</q-item-label>
                    <q-item-label caption>Bayar saat barang diterima</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-icon name="local_atm" color="positive" size="24px" />
                  </q-item-section>
                </q-item>
                
                <q-item tag="label" class="payment-option">
                  <q-item-section avatar>
                    <q-radio v-model="paymentMethod" val="transfer" color="primary" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>Transfer Bank</q-item-label>
                    <q-item-label caption>BCA, Mandiri, BNI, BRI</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-icon name="account_balance" color="primary" size="24px" />
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
          <q-card class="summary-card" flat>
            <q-card-section>
              <h3 class="summary-title">Ringkasan Pesanan</h3>
              
              <!-- Items -->
              <div class="summary-items">
                <div 
                  v-for="item in cartStore.items" 
                  :key="item.id"
                  class="summary-item"
                >
                  <q-img
                    :src="item.image_url || item.product?.image_url"
                    class="item-thumb"
                    fit="contain"
                  />
                  <div class="item-info">
                    <span class="item-name">{{ item.name || item.product?.name }}</span>
                    <span class="item-qty">x{{ item.quantity }}</span>
                  </div>
                  <span class="item-price">{{ formatPrice(item.price * item.quantity) }}</span>
                </div>
              </div>
              
              <q-separator class="q-my-md" />
              
              <div class="summary-row">
                <span>Subtotal</span>
                <span>{{ cartStore.formattedTotal }}</span>
              </div>
              
              <div class="summary-row">
                <span>Ongkos Kirim</span>
                <span class="text-positive">Gratis</span>
              </div>
              
              <q-separator class="q-my-md" />
              
              <div class="summary-row total">
                <span>Total Pembayaran</span>
                <span class="total-price">{{ cartStore.formattedTotal }}</span>
              </div>
            </q-card-section>
            
            <q-card-actions>
              <q-btn
                color="primary"
                label="Buat Pesanan"
                no-caps
                unelevated
                class="full-width checkout-btn"
                :loading="isSubmitting"
                @click="handleCheckout"
              />
            </q-card-actions>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Success Dialog -->
    <q-dialog v-model="showSuccessDialog" persistent>
      <q-card class="success-dialog">
        <q-card-section class="text-center">
          <q-icon name="check_circle" size="80px" color="positive" />
          <h3>Pesanan Berhasil!</h3>
          <p>Pesanan Anda telah berhasil dibuat.</p>
          <p class="order-number">No. Pesanan: {{ orderNumber }}</p>
        </q-card-section>
        <q-card-actions align="center">
          <q-btn
            color="primary"
            label="Lihat Pesanan"
            no-caps
            unelevated
            @click="goToOrders"
          />
          <q-btn
            flat
            label="Belanja Lagi"
            no-caps
            @click="goToShop"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../stores/cart'

const router = useRouter()
const cartStore = useCartStore()

const form = reactive({
  phone: '',
  shipping_address: '',
  notes: ''
})

const paymentMethod = ref('cod')
const isSubmitting = ref(false)
const showSuccessDialog = ref(false)
const orderNumber = ref('')

// Format price
const formatPrice = (price) => {
  return cartStore.formatIDR(price)
}

// Handle checkout
const handleCheckout = async () => {
  if (!form.phone || !form.shipping_address) {
    return
  }
  
  isSubmitting.value = true
  
  const result = await cartStore.checkout({
    phone: form.phone,
    shipping_address: form.shipping_address,
    notes: form.notes
  })
  
  isSubmitting.value = false
  
  if (result.success) {
    orderNumber.value = result.order?.order?.order_number || result.order?.order?.id || 'N/A'
    showSuccessDialog.value = true
  }
}

const goToOrders = () => {
  showSuccessDialog.value = false
  router.push('/orders')
}

const goToShop = () => {
  showSuccessDialog.value = false
  router.push('/shop')
}

// Fetch cart on mount
onMounted(() => {
  cartStore.fetchCart()
})
</script>

<style lang="scss" scoped>
.checkout-page {
  background: #f8fafc;
  min-height: 100vh;
  padding: 24px 16px;
}

.checkout-container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 32px;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1a1a2e;
  margin: 0;
}

.page-subtitle {
  color: #6b7280;
  margin: 0;
}

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

.checkout-content {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 32px;
  align-items: start;
}

.form-card {
  border-radius: 16px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 20px;
  display: flex;
  align-items: center;
}

.payment-option {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  margin-bottom: 12px;
  
  &:last-child {
    margin-bottom: 0;
  }
}

.order-summary {
  position: sticky;
  top: 80px;
}

.summary-card {
  border-radius: 16px;
  background: white;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.summary-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 20px;
}

.summary-items {
  max-height: 300px;
  overflow-y: auto;
}

.summary-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
}

.item-thumb {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  background: #f8fafc;
}

.item-info {
  flex: 1;
  min-width: 0;
}

.item-name {
  display: block;
  font-size: 0.9rem;
  color: #1a1a2e;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-qty {
  font-size: 0.8rem;
  color: #6b7280;
}

.item-price {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1a1a2e;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  color: #6b7280;
  
  &.total {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
  }
}

.total-price {
  font-size: 1.25rem;
  color: #e94560;
}

.checkout-btn {
  border-radius: 12px;
  font-weight: 600;
  padding: 14px 24px;
}

.success-dialog {
  border-radius: 20px;
  padding: 20px;
  min-width: 350px;
  
  h3 {
    margin: 16px 0 8px;
    color: #1a1a2e;
  }
  
  p {
    color: #6b7280;
    margin: 0 0 8px;
  }
  
  .order-number {
    font-weight: 700;
    color: #1a1a2e;
    background: #f3f4f6;
    padding: 8px 16px;
    border-radius: 8px;
    display: inline-block;
  }
}

// Responsive
@media (max-width: 1023px) {
  .checkout-content {
    grid-template-columns: 1fr;
  }
  
  .order-summary {
    position: static;
  }
}

@media (max-width: 599px) {
  .page-title {
    font-size: 1.5rem;
  }
}
</style>
