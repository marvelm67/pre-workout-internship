<template>
  <q-card class="product-card" flat>
    <!-- Product Image -->
    <div class="product-image-container">
      <q-img
        :src="product.image_url"
        :alt="product.name"
        class="product-image"
        fit="contain"
        :ratio="1"
      >
        <template v-slot:loading>
          <div class="absolute-full flex flex-center bg-grey-2">
            <q-spinner color="primary" size="30px" />
          </div>
        </template>
        <template v-slot:error>
          <div class="absolute-full flex flex-center bg-grey-2">
            <q-icon name="broken_image" size="40px" color="grey-5" />
          </div>
        </template>
      </q-img>
      
      <!-- Category Badge -->
      <q-badge 
        class="category-badge" 
        :color="getCategoryColor(product.category_id)"
        text-color="white"
      >
        {{ getCategoryName(product.category_id) }}
      </q-badge>
      
      <!-- Stock Badge -->
      <q-badge 
        v-if="product.stock < 10"
        class="stock-badge"
        :color="product.stock === 0 ? 'negative' : 'warning'"
        text-color="white"
      >
        {{ product.stock === 0 ? 'Habis' : `Sisa ${product.stock}` }}
      </q-badge>
    </div>

    <q-card-section class="product-info">
      <!-- Product Name -->
      <h3 class="product-name">{{ product.name }}</h3>
      
      <!-- Product Description -->
      <p class="product-description">{{ product.description }}</p>
      
      <!-- Price -->
      <div class="product-price">
        {{ formatPrice(product.price) }}
      </div>
    </q-card-section>

    <q-card-actions class="product-actions">
      <q-btn
        class="add-to-cart-btn"
        :color="product.stock === 0 ? 'grey' : 'primary'"
        :disable="product.stock === 0"
        no-caps
        unelevated
        @click="$emit('add-to-cart', product)"
      >
        <q-icon name="add_shopping_cart" class="q-mr-sm" />
        {{ product.stock === 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
      </q-btn>
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { useProductStore } from '../stores/product'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

defineEmits(['add-to-cart'])

const productStore = useProductStore()

// Format price to IDR
const formatPrice = (price) => {
  return productStore.formatPrice(price)
}

// Get category name
const getCategoryName = (categoryId) => {
  return productStore.getCategoryName(categoryId)
}

// Get category color
const getCategoryColor = (categoryId) => {
  const colors = {
    '1': 'amber-9',    // Sunglasses
    '2': 'blue-8',     // Anti Radiasi
    '3': 'teal-7'      // Frame Optik
  }
  return colors[categoryId] || 'grey-7'
}
</script>

<style lang="scss" scoped>
.product-card {
  border-radius: 16px;
  background: white;
  overflow: hidden;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  
  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    
    .product-image {
      transform: scale(1.05);
    }
  }
}

.product-image-container {
  position: relative;
  background: #f8fafc;
  padding: 16px;
  overflow: hidden;
}

.product-image {
  height: 180px;
  transition: transform 0.3s ease;
  
  :deep(img) {
    object-fit: contain !important;
  }
}

.category-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 12px;
}

.stock-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 12px;
}

.product-info {
  flex: 1;
  padding: 16px;
  display: flex;
  flex-direction: column;
}

.product-name {
  font-size: 1rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 8px;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-description {
  font-size: 0.85rem;
  color: #6b7280;
  margin: 0 0 12px;
  line-height: 1.5;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-price {
  font-size: 1.25rem;
  font-weight: 800;
  color: #e94560;
}

.product-actions {
  padding: 0 16px 16px;
}

.add-to-cart-btn {
  width: 100%;
  border-radius: 12px;
  font-weight: 600;
  padding: 10px 16px;
}

// Responsive
@media (max-width: 599px) {
  .product-image {
    height: 140px;
  }
  
  .product-name {
    font-size: 0.9rem;
  }
  
  .product-description {
    font-size: 0.8rem;
    -webkit-line-clamp: 2;
  }
  
  .product-price {
    font-size: 1rem;
  }
  
  .add-to-cart-btn {
    font-size: 0.8rem;
    padding: 8px 12px;
    
    .q-icon {
      display: none;
    }
  }
}
</style>
