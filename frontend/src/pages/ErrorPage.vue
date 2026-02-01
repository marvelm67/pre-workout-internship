<template>
  <q-page class="error-page">
    <div class="error-container">
      <q-icon name="error_outline" size="100px" color="grey-5" />
      <h1 class="error-code">404</h1>
      <h2 class="error-title">Halaman Tidak Ditemukan</h2>
      <p class="error-message">
        Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.
      </p>
      <div class="error-actions">
        <q-btn
          color="primary"
          label="Kembali ke Beranda"
          no-caps
          unelevated
          rounded
          @click="router.push('/shop')"
        />
        <q-btn
          flat
          color="grey-7"
          label="Kembali"
          no-caps
          @click="router.back()"
        />
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<style lang="scss" scoped>
.error-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  padding: 20px;
}

.error-container {
  text-align: center;
  max-width: 500px;
}

.error-code {
  font-size: 6rem;
  font-weight: 900;
  color: #1a1a2e;
  margin: 16px 0;
  line-height: 1;
}

.error-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #374151;
  margin: 0 0 16px;
}

.error-message {
  color: #6b7280;
  font-size: 1rem;
  line-height: 1.6;
  margin: 0 0 32px;
}

.error-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  align-items: center;
}

@media (max-width: 599px) {
  .error-code {
    font-size: 4rem;
  }
  
  .error-title {
    font-size: 1.5rem;
  }
}
</style>
