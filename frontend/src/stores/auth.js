/**
 * Auth Store - Pinia
 * Manages authentication state, user info, and JWT token
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI } from '../services/api'
import { Notify } from 'quasar'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(null)
  const isLoading = ref(false)
  const error = ref(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const username = computed(() => user.value?.username || '')

  // Initialize from localStorage
  const initAuth = () => {
    const savedToken = localStorage.getItem('token')
    const savedUser = localStorage.getItem('user')
    
    if (savedToken && savedUser) {
      token.value = savedToken
      user.value = JSON.parse(savedUser)
    }
  }

  // Actions
  /**
   * Register new user
   * @param {Object} credentials - { username, email, password, confpassword }
   */
  const register = async (credentials) => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await authAPI.register(credentials)
      
      if (response.data.status === 201) {
        Notify.create({
          type: 'positive',
          message: 'Registrasi berhasil! Silakan login.',
          icon: 'check_circle'
        })
        return { success: true, data: response.data }
      }
      
      throw new Error(response.data.message || 'Registrasi gagal')
    } catch (err) {
      const errorMessage = err.response?.data?.message || err.message || 'Registrasi gagal'
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
   * Login user
   * @param {Object} credentials - { email, password }
   */
  const login = async (credentials) => {
    isLoading.value = true
    error.value = null
    
    try {
      const response = await authAPI.login(credentials)
      
      if (response.data.status === 200) {
        const { token: authToken, user: userData } = response.data.data
        
        // Save to state
        token.value = authToken
        user.value = userData
        
        // Save to localStorage
        localStorage.setItem('token', authToken)
        localStorage.setItem('user', JSON.stringify(userData))
        
        Notify.create({
          type: 'positive',
          message: `Selamat datang, ${userData.username}!`,
          icon: 'login'
        })
        
        return { success: true, user: userData }
      }
      
      throw new Error(response.data.message || 'Login gagal')
    } catch (err) {
      const errorMessage = err.response?.data?.message || err.message || 'Login gagal'
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
   * Logout user
   */
  const logout = () => {
    // Clear state
    token.value = null
    user.value = null
    error.value = null
    
    // Clear localStorage
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    
    Notify.create({
      type: 'info',
      message: 'Anda telah logout',
      icon: 'logout'
    })
  }

  /**
   * Fetch current user info
   */
  const fetchMe = async () => {
    if (!token.value) return null
    
    try {
      const response = await authAPI.me()
      if (response.data.status === 200) {
        user.value = response.data.data
        localStorage.setItem('user', JSON.stringify(response.data.data))
        return response.data.data
      }
    } catch (err) {
      // Token invalid, logout
      logout()
    }
    
    return null
  }

  // Initialize on store creation
  initAuth()

  return {
    // State
    user,
    token,
    isLoading,
    error,
    
    // Getters
    isAuthenticated,
    isAdmin,
    username,
    
    // Actions
    initAuth,
    register,
    login,
    logout,
    fetchMe
  }
})
