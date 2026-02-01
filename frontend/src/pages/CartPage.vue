<template>
  <q-page class="cart-page">
    <div class="cart-container">
      <!-- Page Header -->
      <div class="page-header">
        <h1 class="page-title">Keranjang Belanja</h1>
        <p class="page-subtitle">{{ cartStore.itemCount }} item di keranjang Anda</p>
      </div>

      <!-- Loading State -->
      <div v-if="cartStore.isLoading && !cartStore.items.length" class="loading-state">
        <q-spinner-dots size="50px" color="primary" />
        <p>Memuat keranjang...</p>
      </div>

      <!-- Empty Cart -->
      <div v-else-if="cartStore.isEmpty" class="empty-state">
        <q-icon name="shopping_cart" size="80px" color="grey-4" />
        <h3>Keranjang Kosong</h3>
        <p>Belum ada produk di keranjang Anda</p>
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

      <!-- Cart Content -->
      <div v-else class="cart-content">
        <!-- Cart Items -->
        <div class="cart-items">
          <TransitionGroup name="cart-item">
            <q-card 
              v-for="item in cartStore.items" 
              :key="item.id"
              class="cart-item-card"
              flat
            >
              <div class="cart-item">
                <!-- Product Image -->
                <q-img
                  :src="item.image_url || item.product?.image_url"
                  :alt="item.name || item.product?.name"
                  class="item-image"
                  fit="contain"
                >
                  <template v-slot:error>
                    <div class="absolute-full flex flex-center bg-grey-2">
                      <q-icon name="broken_image" size="30px" color="grey-5" />
                    </div>
                  </template>
                </q-img>

                <!-- Item Details -->
                <div class="item-details">
                  <h4 class="item-name">{{ item.name || item.product?.name }}</h4>
                  <p class="item-price">{{ formatPrice(item.price) }}</p>
                </div>

                <!-- Quantity Controls -->
                <div class="quantity-controls">
                  <q-btn
                    flat
                    round
                    dense
                    icon="remove"
                    color="grey-7"
                    :disable="item.quantity <= 1"
                    @click="updateQuantity(item.id, item.quantity - 1)"
                  />
                  <span class="quantity-value">{{ item.quantity }}</span>
                  <q-btn
                    flat
                    round
                    dense
                    icon="add"
                    color="grey-7"
                    @click="updateQuantity(item.id, item.quantity + 1)"
                  />
                </div>

                <!-- Subtotal -->
                <div class="item-subtotal">
                  {{ formatPrice(item.price * item.quantity) }}
                </div>

                <!-- Remove Button -->
                <q-btn
                  flat
                  round
                  dense
                  icon="delete_outline"
                  color="negative"
                  @click="removeItem(item.id)"
                />
              </div>
            </q-card>
          </TransitionGroup>

          <!-- Clear Cart Button -->
          <div class="clear-cart-section">
            <q-btn
              flat
              no-caps
              color="negative"
              label="Kosongkan Keranjang"
              icon="delete_sweep"
              @click="clearCart"
            />
          </div>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
          <q-card class="summary-card" flat>
            <q-card-section>
              <h3 class="summary-title">Ringkasan Pesanan</h3>
              
              <div class="summary-row">
                <span>Subtotal ({{ cartStore.itemCount }} item)</span>
                <span>{{ cartStore.formattedTotal }}</span>
              </div>
              
              <div class="summary-row">
                <span>Ongkos Kirim</span>
                <span class="text-positive">Gratis</span>
              </div>
              
              <q-separator class="q-my-md" />
              
              <div class="summary-row total">
                <span>Total</span>
                <span class="total-price">{{ cartStore.formattedTotal }}</span>
              </div>
            </q-card-section>
            
            <q-card-actions vertical>
              <q-btn
                color="primary"
                label="Lanjut ke Checkout"
                no-caps
                unelevated
                class="checkout-btn"
                @click="router.push('/checkout')"
              />
              
              <q-btn
                flat
                no-caps
                color="grey-7"
                label="Lanjut Belanja"
                class="q-mt-sm"
                @click="router.push('/shop')"
              />
            </q-card-actions>
          </q-card>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '../stores/cart'
import { Dialog } from 'quasar'

const router = useRouter()
const cartStore = useCartStore()

// Format price
const formatPrice = (price) => {
  return cartStore.formatIDR(price)
}

// Update quantity
const updateQuantity = async (itemId, quantity) => {
  await cartStore.updateQuantity(itemId, quantity)
}

// Remove item
const removeItem = async (itemId) => {
  Dialog.create({
    title: 'Hapus Item',
    message: 'Apakah Anda yakin ingin menghapus item ini dari keranjang?',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    await cartStore.removeFromCart(itemId)
  })
}

// Clear cart
const clearCart = () => {
  Dialog.create({
    title: 'Kosongkan Keranjang',
    message: 'Apakah Anda yakin ingin mengosongkan keranjang?',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    await cartStore.clearCart()
  })
}

// Fetch cart on mount
onMounted(() => {
  cartStore.fetchCart()
})
</script>

<style lang="scss" scoped>
.cart-page {
  background: #f8fafc;
  min-height: 100vh;
  padding: 24px 16px;
}

.cart-container {
  max-width: 1200px;
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

.cart-content {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 32px;
  align-items: start;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cart-item-card {
  border-radius: 16px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.cart-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
}

.item-image {
  width: 100px;
  height: 100px;
  border-radius: 12px;
  background: #f8fafc;
  flex-shrink: 0;
}

.item-details {
  flex: 1;
  min-width: 0;
}

.item-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0 0 8px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.item-price {
  font-size: 0.9rem;
  color: #6b7280;
  margin: 0;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f3f4f6;
  border-radius: 25px;
  padding: 4px;
}

.quantity-value {
  width: 32px;
  text-align: center;
  font-weight: 600;
  color: #1a1a2e;
}

.item-subtotal {
  font-size: 1.1rem;
  font-weight: 700;
  color: #e94560;
  min-width: 120px;
  text-align: right;
}

.clear-cart-section {
  display: flex;
  justify-content: flex-end;
  padding-top: 8px;
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
  padding: 12px 24px;
}

// Cart item animations
.cart-item-enter-active,
.cart-item-leave-active {
  transition: all 0.3s ease;
}

.cart-item-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.cart-item-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

// Responsive
@media (max-width: 1023px) {
  .cart-content {
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
  
  .cart-item {
    flex-wrap: wrap;
    padding: 12px;
  }
  
  .item-image {
    width: 80px;
    height: 80px;
  }
  
  .item-details {
    flex: 1;
    min-width: calc(100% - 100px);
  }
  
  .quantity-controls {
    order: 3;
    margin-top: 8px;
  }
  
  .item-subtotal {
    order: 4;
    margin-top: 8px;
    flex: 1;
    text-align: left;
  }
}
</style>
