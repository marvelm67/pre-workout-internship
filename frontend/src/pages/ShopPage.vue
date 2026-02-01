<template>
  <q-page class="shop-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-content">
        <h1 class="hero-title">Koleksi Kacamata Premium</h1>
        <p class="hero-subtitle">Temukan gaya Anda dengan koleksi eyewear terbaik</p>
      </div>
    </section>

    <!-- Main Content -->
    <div class="shop-container">
      <!-- Category Tabs -->
      <div class="category-section">
        <q-tabs
          v-model="selectedTab"
          class="category-tabs"
          active-color="white"
          active-bg-color="primary"
          indicator-color="transparent"
          no-caps
        >
          <q-tab 
            name="all" 
            label="Semua" 
            icon="apps"
          />
          <q-tab 
            v-for="category in productStore.categories" 
            :key="category.id"
            :name="category.id" 
            :label="category.name"
            :icon="category.icon"
          />
        </q-tabs>
      </div>

      <!-- Loading State -->
      <div v-if="productStore.isLoading" class="loading-state">
        <q-spinner-dots size="50px" color="primary" />
        <p>Memuat produk...</p>
      </div>

      <!-- Products Grid -->
      <div v-else class="products-grid">
        <TransitionGroup name="product-list">
          <ProductCard
            v-for="product in filteredProducts"
            :key="product.id"
            :product="product"
            @add-to-cart="handleAddToCart"
          />
        </TransitionGroup>
      </div>

      <!-- Empty State -->
      <div v-if="!productStore.isLoading && filteredProducts.length === 0" class="empty-state">
        <q-icon name="search_off" size="64px" color="grey-5" />
        <h3>Tidak ada produk</h3>
        <p>Coba pilih kategori lain</p>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useProductStore } from '../stores/product'
import { useCartStore } from '../stores/cart'
import { useAuthStore } from '../stores/auth'
import ProductCard from '../components/ProductCard.vue'

const router = useRouter()
const productStore = useProductStore()
const cartStore = useCartStore()
const authStore = useAuthStore()

const selectedTab = ref('all')

// Computed filtered products
const filteredProducts = computed(() => {
  if (selectedTab.value === 'all') {
    return productStore.products
  }
  return productStore.products.filter(p => p.category_id === selectedTab.value)
})

// Watch tab changes
watch(selectedTab, (newVal) => {
  productStore.setCategory(newVal === 'all' ? null : newVal)
})

// Handle add to cart
const handleAddToCart = async (product) => {
  if (!authStore.isAuthenticated) {
    router.push('/login?redirect=/shop')
    return
  }
  
  await cartStore.addToCart(product.id, 1, product)
}

// Fetch products on mount
onMounted(() => {
  productStore.fetchProducts()
})
</script>

<style lang="scss" scoped>
.shop-page {
  background: #f8fafc;
  min-height: 100vh;
}

.hero-section {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
  padding: 60px 20px;
  text-align: center;
  position: relative;
  overflow: hidden;
  
  &::before {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    background: rgba(233, 69, 96, 0.1);
    border-radius: 50%;
    top: -100px;
    right: -50px;
  }
  
  &::after {
    content: '';
    position: absolute;
    width: 200px;
    height: 200px;
    background: rgba(233, 69, 96, 0.05);
    border-radius: 50%;
    bottom: -50px;
    left: -30px;
  }
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 600px;
  margin: 0 auto;
}

.hero-title {
  font-size: 2.5rem;
  font-weight: 800;
  color: white;
  margin: 0 0 16px;
  letter-spacing: -0.5px;
}

.hero-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
}

.shop-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 32px 16px;
}

.category-section {
  margin-bottom: 32px;
  display: flex;
  justify-content: center;
}

.category-tabs {
  background: white;
  border-radius: 30px;
  padding: 6px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  
  :deep(.q-tab) {
    border-radius: 24px;
    margin: 0 4px;
    min-height: 44px;
    
    &.q-tab--active {
      background: #1a1a2e;
      color: white;
    }
  }
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 20px;
  
  p {
    margin-top: 16px;
    color: #6b7280;
  }
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}

.empty-state {
  text-align: center;
  padding: 80px 20px;
  
  h3 {
    margin: 16px 0 8px;
    color: #374151;
  }
  
  p {
    color: #6b7280;
    margin: 0;
  }
}

// Product list animations
.product-list-enter-active,
.product-list-leave-active {
  transition: all 0.3s ease;
}

.product-list-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.product-list-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

// Responsive
@media (max-width: 599px) {
  .hero-title {
    font-size: 1.75rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
  }
  
  .hero-section {
    padding: 40px 16px;
  }
  
  .category-tabs {
    :deep(.q-tab) {
      padding: 0 12px;
      font-size: 0.85rem;
    }
    
    :deep(.q-tab__label) {
      display: none;
    }
  }
  
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
  }
}

@media (min-width: 600px) and (max-width: 1023px) {
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
  }
}
</style>
