<template>
  <div class="register-page">
    <!-- Background decoration -->
    <div class="bg-decoration">
      <div class="circle circle-1"></div>
      <div class="circle circle-2"></div>
    </div>

    <div class="register-container">
      <!-- Brand Section -->
      <div class="brand-section">
        <q-icon name="visibility" size="48px" color="white" />
        <h1 class="brand-title">OpticModern</h1>
        <p class="brand-subtitle">Buat Akun Baru</p>
      </div>

      <!-- Register Card -->
      <q-card class="register-card" flat>
        <q-card-section class="text-center q-pb-none">
          <h2 class="form-title">Daftar</h2>
          <p class="form-subtitle">Isi data untuk membuat akun baru</p>
        </q-card-section>

        <q-card-section class="q-pt-md">
          <q-form @submit.prevent="handleRegister" class="q-gutter-md">
            <!-- Username Input -->
            <q-input
              v-model="form.username"
              type="text"
              label="Username"
              outlined
              rounded
              :rules="[
                val => !!val || 'Username wajib diisi',
                val => val.length >= 3 || 'Username minimal 3 karakter'
              ]"
              lazy-rules
            >
              <template v-slot:prepend>
                <q-icon name="person" color="grey-7" />
              </template>
            </q-input>

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

            <!-- Confirm Password Input -->
            <q-input
              v-model="form.confpassword"
              :type="showConfPassword ? 'text' : 'password'"
              label="Konfirmasi Password"
              outlined
              rounded
              :rules="[
                val => !!val || 'Konfirmasi password wajib diisi',
                val => val === form.password || 'Password tidak cocok'
              ]"
              lazy-rules
            >
              <template v-slot:prepend>
                <q-icon name="lock_outline" color="grey-7" />
              </template>
              <template v-slot:append>
                <q-icon
                  :name="showConfPassword ? 'visibility_off' : 'visibility'"
                  class="cursor-pointer"
                  color="grey-7"
                  @click="showConfPassword = !showConfPassword"
                />
              </template>
            </q-input>

            <!-- Role Selection (Optional - for demo) -->
            <q-select
              v-model="form.role"
              :options="roleOptions"
              label="Daftar sebagai"
              outlined
              rounded
              emit-value
              map-options
            >
              <template v-slot:prepend>
                <q-icon name="badge" color="grey-7" />
              </template>
            </q-select>

            <!-- Terms Agreement -->
            <q-checkbox 
              v-model="agreeTerms" 
              label="Saya setuju dengan Syarat & Ketentuan"
              color="primary"
              dense
            />

            <!-- Register Button -->
            <q-btn
              type="submit"
              label="Daftar"
              color="primary"
              class="full-width register-btn"
              rounded
              size="lg"
              :loading="authStore.isLoading"
              :disable="!agreeTerms"
              no-caps
            />
          </q-form>
        </q-card-section>

        <q-card-section class="text-center q-pt-sm">
          <p class="login-text">
            Sudah punya akun?
            <router-link to="/login" class="login-link">Masuk sekarang</router-link>
          </p>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// Form state
const form = reactive({
  username: '',
  email: '',
  password: '',
  confpassword: '',
  role: 'user'
})

const showPassword = ref(false)
const showConfPassword = ref(false)
const agreeTerms = ref(false)

const roleOptions = [
  { label: 'User (Pembeli)', value: 'user' },
  { label: 'Admin (Demo)', value: 'admin' }
]

// Validation helper
const isValidEmail = (email) => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

// Handle registration
const handleRegister = async () => {
  const result = await authStore.register({
    username: form.username,
    email: form.email,
    password: form.password,
    confpassword: form.confpassword,
    role: form.role
  })

  if (result.success) {
    // Redirect to login page
    router.push('/login')
  }
}
</script>

<style lang="scss" scoped>
.register-page {
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
    left: -100px;
  }
  
  .circle-2 {
    width: 300px;
    height: 300px;
    bottom: -50px;
    right: -50px;
  }
}

.register-container {
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

.register-card {
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

.register-btn {
  margin-top: 8px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.login-text {
  color: #6b7280;
  margin: 0;
  font-size: 0.95rem;
}

.login-link {
  color: #e94560;
  font-weight: 600;
  text-decoration: none;
  
  &:hover {
    text-decoration: underline;
  }
}

// Responsive
@media (max-width: 599px) {
  .brand-title {
    font-size: 2rem;
  }
  
  .register-card {
    padding: 12px;
  }
}
</style>
