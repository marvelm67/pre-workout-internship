/**
 * Product Store - Pinia
 * Manages products state with mock data fallback
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { productsAPI } from '../services/api'
import { Notify } from 'quasar'

// Mock data as specified in requirements
const MOCK_PRODUCTS = [
  { 
    id: 1,
    category_id: "1", 
    name: "Clubmaster Retro Wood", 
    description: "Desain semi-rimless vintage dengan aksen kayu pada bagian temple.", 
    price: 1100000, 
    stock: 10, 
    image_url: "https://images.ray-ban.com/is/image/RayBan/805289653653_shad_qt.png" 
  },
  { 
    id: 2,
    category_id: "1", 
    name: "Cat-Eye Vogue Edition", 
    description: "Frame cat-eye elegan dengan sudut tajam untuk tampilan bold.", 
    price: 750000, 
    stock: 25, 
    image_url: "https://image4.cdnsbg.com/2/59/412948_1599520038228.jpg" 
  },
  { 
    id: 3,
    category_id: "2", 
    name: "Office Shield Square", 
    description: "Lensa bluelight transparan dengan frame kotak minimalis.", 
    price: 350000, 
    stock: 40, 
    image_url: "https://m.media-amazon.com/images/I/61B+r3gME7L._AC_UY1000_.jpg" 
  },
  { 
    id: 4,
    category_id: "2", 
    name: "Amber Night Gamer", 
    description: "Lensa kuning khusus untuk memblokir cahaya biru saat gaming.", 
    price: 550000, 
    stock: 15, 
    image_url: "https://m.media-amazon.com/images/I/71r6qnKwmrL._AC_UF1000,1000_QL80_.jpg" 
  },
  { 
    id: 5,
    category_id: "3", 
    name: "Acetate Clear Crystal", 
    description: "Frame transparan dari bahan asetat premium.", 
    price: 650000, 
    stock: 18, 
    image_url: "https://assets2.glasses.com/cdn-record-files-pi/clear_frame.png" 
  },
  { 
    id: 6,
    category_id: "3", 
    name: "Carbon Fiber Sport", 
    description: "Teknologi serat karbon yang fleksibel dan kuat.", 
    price: 1450000, 
    stock: 8, 
    image_url: "https://www.queshark.com/cdn/shop/products/t1.jpg" 
  },
  { 
    id: 7,
    category_id: "3", 
    name: "Rose Gold Geometric", 
    description: "Frame logam tipis berbentuk hexagonal finishing rose gold.", 
    price: 900000, 
    stock: 22, 
    image_url: "https://cdn-images.farfetch-contents.com/15/91/22/41/15912241_29408737_600.jpg" 
  }
]

const CATEGORIES = [
  { id: "1", name: "Sunglasses", icon: "wb_sunny" },
  { id: "2", name: "Anti Radiasi", icon: "computer" },
  { id: "3", name: "Frame Optik", icon: "visibility" }
]

export const useProductStore = defineStore('product', () => {
  // State
  const products = ref([])
  const categories = ref(CATEGORIES)
  const selectedCategory = ref(null) // null = show all
  const isLoading = ref(false)
  const error = ref(null)
  const useMockData = ref(true) // Toggle for mock/API data

  // Getters
  const filteredProducts = computed(() => {
    if (!selectedCategory.value) {
      return products.value
    }
    return products.value.filter(p => p.category_id === selectedCategory.value)
  })

  const getProductById = computed(() => {
    return (id) => products.value.find(p => p.id === parseInt(id))
  })

  const getCategoryName = computed(() => {
    return (categoryId) => {
      const category = categories.value.find(c => c.id === categoryId)
      return category?.name || 'Unknown'
    }
  })

  // Actions
  /**
   * Fetch products from API or use mock data
   */
  const fetchProducts = async () => {
    isLoading.value = true
    error.value = null
    
    try {
      if (useMockData.value) {
        // Use mock data
        await new Promise(resolve => setTimeout(resolve, 500)) // Simulate network delay
        products.value = MOCK_PRODUCTS
      } else {
        // Fetch from API
        const response = await productsAPI.getAll()
        
        if (response.data.status === 200) {
          products.value = response.data.data || []
        }
      }
    } catch (err) {
      // Fallback to mock data on API error
      console.warn('API fetch failed, using mock data:', err.message)
      products.value = MOCK_PRODUCTS
      error.value = 'Menggunakan data lokal'
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Set selected category filter
   * @param {string|null} categoryId - Category ID or null for all
   */
  const setCategory = (categoryId) => {
    selectedCategory.value = categoryId
  }

  /**
   * Create product (Admin only)
   * @param {Object} productData - Product data
   */
  const createProduct = async (productData) => {
    isLoading.value = true
    error.value = null
    
    try {
      if (useMockData.value) {
        // Mock create
        const newId = Math.max(...products.value.map(p => p.id)) + 1
        const newProduct = { ...productData, id: newId }
        products.value.push(newProduct)
        
        Notify.create({
          type: 'positive',
          message: 'Produk berhasil ditambahkan',
          icon: 'check_circle'
        })
        
        return { success: true, product: newProduct }
      } else {
        const response = await productsAPI.create(productData)
        
        if (response.data.status === 201) {
          products.value.push(response.data.data)
          
          Notify.create({
            type: 'positive',
            message: 'Produk berhasil ditambahkan',
            icon: 'check_circle'
          })
          
          return { success: true, product: response.data.data }
        }
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Gagal menambahkan produk'
      error.value = errorMessage
      
      Notify.create({
        type: 'negative',
        message: errorMessage,
        icon: 'error'
      })
      
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Update product (Admin only)
   * @param {number} id - Product ID
   * @param {Object} productData - Updated product data
   */
  const updateProduct = async (id, productData) => {
    isLoading.value = true
    error.value = null
    
    try {
      if (useMockData.value) {
        // Mock update
        const index = products.value.findIndex(p => p.id === id)
        if (index !== -1) {
          products.value[index] = { ...products.value[index], ...productData }
          
          Notify.create({
            type: 'positive',
            message: 'Produk berhasil diperbarui',
            icon: 'check_circle'
          })
          
          return { success: true, product: products.value[index] }
        }
      } else {
        const response = await productsAPI.update(id, productData)
        
        if (response.data.status === 200) {
          const index = products.value.findIndex(p => p.id === id)
          if (index !== -1) {
            products.value[index] = response.data.data
          }
          
          Notify.create({
            type: 'positive',
            message: 'Produk berhasil diperbarui',
            icon: 'check_circle'
          })
          
          return { success: true, product: response.data.data }
        }
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Gagal memperbarui produk'
      error.value = errorMessage
      
      Notify.create({
        type: 'negative',
        message: errorMessage,
        icon: 'error'
      })
      
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Delete product (Admin only)
   * @param {number} id - Product ID
   */
  const deleteProduct = async (id) => {
    isLoading.value = true
    error.value = null
    
    try {
      if (useMockData.value) {
        // Mock delete
        const index = products.value.findIndex(p => p.id === id)
        if (index !== -1) {
          products.value.splice(index, 1)
          
          Notify.create({
            type: 'positive',
            message: 'Produk berhasil dihapus',
            icon: 'check_circle'
          })
          
          return { success: true }
        }
      } else {
        const response = await productsAPI.delete(id)
        
        if (response.data.status === 200) {
          const index = products.value.findIndex(p => p.id === id)
          if (index !== -1) {
            products.value.splice(index, 1)
          }
          
          Notify.create({
            type: 'positive',
            message: 'Produk berhasil dihapus',
            icon: 'check_circle'
          })
          
          return { success: true }
        }
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Gagal menghapus produk'
      error.value = errorMessage
      
      Notify.create({
        type: 'negative',
        message: errorMessage,
        icon: 'error'
      })
      
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Format price to IDR
   * @param {number} price - Price value
   */
  const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(price)
  }

  return {
    // State
    products,
    categories,
    selectedCategory,
    isLoading,
    error,
    useMockData,
    
    // Getters
    filteredProducts,
    getProductById,
    getCategoryName,
    
    // Actions
    fetchProducts,
    setCategory,
    createProduct,
    updateProduct,
    deleteProduct,
    formatPrice
  }
})
