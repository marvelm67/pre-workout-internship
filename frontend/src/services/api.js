/**
 * Centralized API Service
 * All API logic using Axios with JWT Interceptors
 */

import axios from 'axios'
import { useAuthStore } from '../stores/auth'

// Create axios instance
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8080',
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor - Add JWT token to requests
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - Handle token expiration
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid - logout user
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// ==================== AUTH API ====================

export const authAPI = {
  /**
   * Register new user
   * @param {Object} data - { username, email, password, confpassword, role? }
   */
  register: (data) => api.post('/register', data),

  /**
   * Login user
   * @param {Object} data - { email, password }
   */
  login: (data) => api.post('/login', data),

  /**
   * Get current user info
   */
  me: () => api.get('/me')
}

// ==================== PRODUCTS API ====================

export const productsAPI = {
  /**
   * Get all products
   * @param {Object} params - { limit?, offset? }
   */
  getAll: (params = {}) => api.get('/products', { params }),

  /**
   * Get single product by ID
   * @param {number} id - Product ID
   */
  getById: (id) => api.get(`/products/${id}`),

  /**
   * Create new product (Admin only)
   * @param {Object} data - Product data
   */
  create: (data) => api.post('/products', data),

  /**
   * Update product (Admin only)
   * @param {number} id - Product ID
   * @param {Object} data - Updated product data
   */
  update: (id, data) => api.put(`/products/${id}`, data),

  /**
   * Delete product (Admin only)
   * @param {number} id - Product ID
   */
  delete: (id) => api.delete(`/products/${id}`)
}

// ==================== CART API ====================

export const cartAPI = {
  /**
   * Get cart items
   */
  get: () => api.get('/cart'),

  /**
   * Add item to cart
   * @param {Object} data - { product_id, quantity }
   */
  add: (data) => api.post('/cart', data),

  /**
   * Update cart item quantity
   * @param {number} id - Cart item ID
   * @param {Object} data - { quantity }
   */
  update: (id, data) => api.put(`/cart/${id}`, data),

  /**
   * Remove item from cart
   * @param {number} id - Cart item ID
   */
  remove: (id) => api.delete(`/cart/${id}`),

  /**
   * Clear entire cart
   */
  clear: () => api.delete('/cart/clear')
}

// ==================== ORDERS API ====================

export const ordersAPI = {
  /**
   * Get all orders (User sees own, Admin sees all)
   */
  getAll: () => api.get('/orders'),

  /**
   * Get single order by ID
   * @param {string} id - Order ID (hash)
   */
  getById: (id) => api.get(`/orders/${id}`),

  /**
   * Checkout - Create order from cart
   * @param {Object} data - { shipping_address, phone, notes? }
   */
  checkout: (data) => api.post('/checkout', data),

  /**
   * Update order status (Admin only)
   * @param {string} id - Order ID
   * @param {Object} data - { status }
   */
  updateStatus: (id, data) => api.patch(`/orders/${id}`, data),

  /**
   * Get order statistics (Admin only)
   */
  getStats: () => api.get('/orders/stats')
}

// ==================== ADMIN API ====================

export const adminAPI = {
  /**
   * Get all users (Admin only)
   */
  getUsers: () => api.get('/admin/users'),

  /**
   * Get single user (Admin only)
   * @param {number} id - User ID
   */
  getUser: (id) => api.get(`/admin/users/${id}`),

  /**
   * Update user (Admin only)
   * @param {number} id - User ID
   * @param {Object} data - Updated user data
   */
  updateUser: (id, data) => api.put(`/admin/users/${id}`, data),

  /**
   * Delete user (Admin only)
   * @param {number} id - User ID
   */
  deleteUser: (id) => api.delete(`/admin/users/${id}`),

  /**
   * Get dashboard statistics (Admin only)
   */
  getDashboardStats: () => api.get('/admin/dashboard')
}

export default api
