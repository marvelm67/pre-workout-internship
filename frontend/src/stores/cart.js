/**
 * Cart Store - Pinia
 * Manages shopping cart state with API integration
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { cartAPI, ordersAPI } from '../services/api'
import { Notify } from 'quasar'

export const useCartStore = defineStore('cart', () => {
  // State
  const items = ref([])
  const total = ref(0)
  const isLoading = ref(false)
  const error = ref(null)

  // Getters
  const itemCount = computed(() => {
    return items.value.reduce((sum, item) => sum + item.quantity, 0)
  })

  const isEmpty = computed(() => items.value.length === 0)

  const formattedTotal = computed(() => {
    return formatIDR(total.value)
  })

  // Helper function to format IDR
  const formatIDR = (value) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(value)
  }

  // Actions
  /**
   * Fetch cart from API
   */
  const fetchCart = async () => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await cartAPI.get()
      
      if (response.data.status === 200) {
        items.value = response.data.data.items || []
        total.value = response.data.data.total || 0
      }
    } catch (err) {
      // Don't show error for unauthorized (user not logged in)
      if (err.response?.status !== 401) {
        error.value = err.response?.data?.message || 'Gagal memuat keranjang'
      }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Add item to cart
   * @param {number} productId - Product ID
   * @param {number} quantity - Quantity to add
   * @param {Object} product - Product info for notification
   */
  const addToCart = async (productId, quantity = 1, product = null) => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await cartAPI.add({
        product_id: productId,
        quantity: quantity
      })
      
      if (response.data.status === 200) {
        items.value = response.data.data.items || []
        total.value = response.data.data.total || 0
        
        Notify.create({
          type: 'positive',
          message: `${product?.name || 'Produk'} ditambahkan ke keranjang`,
          icon: 'shopping_cart',
          actions: [
            { label: 'Lihat', color: 'white', handler: () => window.location.href = '/cart' }
          ]
        })
        
        return { success: true }
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Gagal menambahkan ke keranjang'
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
   * Update cart item quantity
   * @param {number} cartItemId - Cart item ID
   * @param {number} quantity - New quantity
   */
  const updateQuantity = async (cartItemId, quantity) => {
    if (quantity < 1) return removeFromCart(cartItemId)
    
    isLoading.value = true
    error.value = null
    
    try {
      const response = await cartAPI.update(cartItemId, { quantity })
      
      if (response.data.status === 200) {
        items.value = response.data.data.items || []
        total.value = response.data.data.total || 0
        return { success: true }
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Gagal mengupdate keranjang'
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
   * Remove item from cart
   * @param {number} cartItemId - Cart item ID
   */
  const removeFromCart = async (cartItemId) => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await cartAPI.remove(cartItemId)
      
      if (response.data.status === 200) {
        items.value = response.data.data.items || []
        total.value = response.data.data.total || 0
        
        Notify.create({
          type: 'info',
          message: 'Produk dihapus dari keranjang',
          icon: 'remove_shopping_cart'
        })
        
        return { success: true }
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Gagal menghapus dari keranjang'
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
   * Clear entire cart
   */
  const clearCart = async () => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await cartAPI.clear()
      
      if (response.data.status === 200) {
        items.value = []
        total.value = 0
        
        Notify.create({
          type: 'info',
          message: 'Keranjang telah dikosongkan',
          icon: 'remove_shopping_cart'
        })
        
        return { success: true }
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Gagal mengosongkan keranjang'
      error.value = errorMessage
      
      return { success: false, error: errorMessage }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Checkout - Create order from cart
   * @param {Object} checkoutData - { shipping_address, phone, notes }
   */
  const checkout = async (checkoutData) => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await ordersAPI.checkout(checkoutData)
      
      if (response.data.status === 201) {
        // Clear local cart state
        items.value = []
        total.value = 0
        
        Notify.create({
          type: 'positive',
          message: 'Pesanan berhasil dibuat!',
          icon: 'check_circle',
          timeout: 4000
        })
        
        return { success: true, order: response.data.data }
      }
    } catch (err) {
      const errorMessage = err.response?.data?.message || 'Checkout gagal'
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

  return {
    // State
    items,
    total,
    isLoading,
    error,
    
    // Getters
    itemCount,
    isEmpty,
    formattedTotal,
    
    // Helper
    formatIDR,
    
    // Actions
    fetchCart,
    addToCart,
    updateQuantity,
    removeFromCart,
    clearCart,
    checkout
  }
})
