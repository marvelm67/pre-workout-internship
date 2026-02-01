<template>
  <div class="login-page">
    <!-- Background decoration -->
    <div class="bg-decoration">
      <div class="circle circle-1"></div>
      <div class="circle circle-2"></div>
    </div>

    <div class="login-container">
      <!-- Brand Section -->
      <div class="brand-section">
        <q-icon name="visibility" size="48px" color="white" />
        <h1 class="brand-title">OpticModern</h1>
        <p class="brand-subtitle">Premium Eyewear Store</p>
      </div>

      <!-- Login Card -->
      <q-card class="login-card" flat>
        <q-card-section class="text-center q-pb-none">
          <h2 class="form-title">Selamat Datang</h2>
          <p class="form-subtitle">Masuk ke akun Anda untuk melanjutkan</p>
        </q-card-section>

        <q-card-section class="q-pt-md">
          <q-form @submit.prevent="handleLogin" class="q-gutter-md">
            <!-- Email Input -->
            <q-input
              v-model="form.email"
              type="email"
              label="Email"
              outlined
              rounded
              :rules="[
                val => !!val || 'Email wajib diisi',
                val => isValidEmail(val) || 'Format email tidak valid'
              ]"
              lazy-rules
            >
              <template v-slot:prepend>
                <q-icon name="email" color="grey-7" />
              </template>
            </q-input>

            <!-- Password Input -->
            <q-input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              label="Password"
              outlined
              rounded
              :rules="[
                val => !!val || 'Password wajib diisi',
                val => val.length >= 6 || 'Password minimal 6 karakter'
              ]"
              lazy-rules
            >
              <template v-slot:prepend>
                <q-icon name="lock" color="grey-7" />
              </template>
              <template v-slot:append>
                <q-icon
                  :name="showPassword ? 'visibility_off' : 'visibility'"
                  class="cursor-pointer"
                  color="grey-7"
                  @click="showPassword = !showPassword"
                />
              </template>
            </q-input>

            <!-- Remember & Forgot -->
            <div class="row items-center justify-between">
              <q-checkbox 
                v-model="rememberMe" 
                label="Ingat saya" 
                color="primary"
                dense
              />
              <a href="#" class="forgot-link">Lupa password?</a>
            </div>

            <!-- Login Button -->
            <q-btn
              type="submit"
              label="Masuk"
              color="primary"
              class="full-width login-btn"
              rounded
              size="lg"
              :loading="authStore.isLoading"
              no-caps
            />
          </q-form>
        </q-card-section>

        <q-card-section class="text-center q-pt-sm">
          <p class="register-text">
            Belum punya akun?
            <router-link to="/register" class="register-link">Daftar sekarang</router-link>
          </p>
        </q-card-section>

        <!-- Demo Credentials -->
        <q-card-section class="demo-section q-pt-none">
          <q-expansion-item
            label="Demo Credentials"
            icon="info"
            dense
            header-class="text-grey-7"
          >
            <q-card>
              <q-card-section class="q-pa-sm">
                <div class="demo-cred">
                  <strong>Admin:</strong>
                  <span>admin@optic.com / password123</span>
                </div>
                <div class="demo-cred">
                  <strong>User:</strong>
                  <span>user@optic.com / password123</span>
                </div>
              </q-card-section>
            </q-card>
          </q-expansion-item>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// Form state
const form = reactive({
  email: '',
  password: ''
})

const showPassword = ref(false)
const rememberMe = ref(false)

// Validation helper
const isValidEmail = (email) => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

// Handle login
const handleLogin = async () => {
  const result = await authStore.login({
    email: form.email,
    password: form.password
  })

  if (result.success) {
    // Redirect based on role
    const redirect = route.query.redirect
    
    if (redirect) {
      router.push(redirect)
    } else if (authStore.isAdmin) {
      router.push('/admin/dashboard')
    } else {
      router.push('/shop')
    }
  }
}
</script>

<style lang="scss" scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
  padding: 20px;
  position: relative;
  overflow: hidden;
}

.bg-decoration {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  overflow: hidden;
  pointer-events: none;
  
  .circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(233, 69, 96, 0.1);
  }
  
  .circle-1 {
    width: 400px;
    height: 400px;
    top: -100px;
    right: -100px;
  }
  
  .circle-2 {
    width: 300px;
    height: 300px;
    bottom: -50px;
    left: -50px;
  }
}

.login-container {
  width: 100%;
  max-width: 420px;
  z-index: 1;
}

.brand-section {
  text-align: center;
  margin-bottom: 32px;
  
  .brand-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
    margin: 16px 0 8px;
    letter-spacing: 1px;
  }
  
  .brand-subtitle {
    color: rgba(255, 255, 255, 0.7);
    font-size: 1rem;
    margin: 0;
  }
}

.login-card {
  border-radius: 24px;
  padding: 16px;
  background: white;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.form-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 8px;
}

.form-subtitle {
  color: #6b7280;
  margin: 0;
  font-size: 0.95rem;
}

.forgot-link {
  color: #e94560;
  text-decoration: none;
  font-size: 0.9rem;
  
  &:hover {
    text-decoration: underline;
  }
}

.login-btn {
  margin-top: 8px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.register-text {
  color: #6b7280;
  margin: 0;
  font-size: 0.95rem;
}

.register-link {
  color: #e94560;
  font-weight: 600;
  text-decoration: none;
  
  &:hover {
    text-decoration: underline;
  }
}

.demo-section {
  border-top: 1px solid #e5e7eb;
  margin-top: 8px;
  padding-top: 8px;
}

.demo-cred {
  font-size: 0.85rem;
  padding: 4px 0;
  
  strong {
    color: #1a1a2e;
    margin-right: 8px;
  }
  
  span {
    color: #6b7280;
    font-family: monospace;
  }
}

// Responsive
@media (max-width: 599px) {
  .brand-title {
    font-size: 2rem;
  }
  
  .login-card {
    padding: 12px;
  }
}
</style>
