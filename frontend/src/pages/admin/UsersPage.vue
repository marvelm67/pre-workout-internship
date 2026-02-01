<template>
  <q-page class="admin-users">
    <div class="users-container">
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Kelola Pengguna</h1>
          <p class="page-subtitle">Lihat dan kelola pengguna terdaftar</p>
        </div>
      </div>

      <!-- Users Table -->
      <q-card class="users-card" flat>
        <q-card-section>
          <!-- Search -->
          <div class="table-controls">
            <q-input
              v-model="search"
              placeholder="Cari pengguna..."
              outlined
              dense
              class="search-input"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
            
            <q-select
              v-model="filterRole"
              :options="roleOptions"
              label="Role"
              outlined
              dense
              emit-value
              map-options
              class="filter-select"
            />
          </div>

          <!-- Table -->
          <q-table
            :rows="filteredUsers"
            :columns="columns"
            row-key="id"
            flat
            :loading="isLoading"
            :pagination="{ rowsPerPage: 10 }"
          >
            <template v-slot:body-cell-avatar="props">
              <q-td :props="props">
                <q-avatar color="primary" text-color="white" size="40px">
                  {{ (props.row.username || 'U').charAt(0).toUpperCase() }}
                </q-avatar>
              </q-td>
            </template>

            <template v-slot:body-cell-role="props">
              <q-td :props="props">
                <q-badge 
                  :color="props.value === 'admin' ? 'negative' : 'primary'"
                  text-color="white"
                >
                  {{ props.value === 'admin' ? 'Admin' : 'User' }}
                </q-badge>
              </q-td>
            </template>

            <template v-slot:body-cell-created_at="props">
              <q-td :props="props">
                {{ formatDate(props.value) }}
              </q-td>
            </template>

            <template v-slot:body-cell-actions="props">
              <q-td :props="props">
                <q-btn
                  flat
                  round
                  dense
                  icon="edit"
                  color="primary"
                  @click="openEditDialog(props.row)"
                />
                <q-btn
                  flat
                  round
                  dense
                  icon="delete"
                  color="negative"
                  @click="confirmDelete(props.row)"
                  :disable="props.row.id === authStore.user?.id"
                />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <!-- Edit User Dialog -->
    <q-dialog v-model="showEditDialog" persistent>
      <q-card class="edit-dialog">
        <q-card-section class="dialog-header">
          <h3>Edit Pengguna</h3>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-card-section v-if="editingUser">
          <q-form @submit.prevent="saveUser" class="q-gutter-md">
            <q-input
              v-model="editForm.username"
              label="Username"
              outlined
              :rules="[val => !!val || 'Username wajib diisi']"
            />

            <q-input
              v-model="editForm.email"
              label="Email"
              type="email"
              outlined
              :rules="[val => !!val || 'Email wajib diisi']"
            />

            <q-select
              v-model="editForm.role"
              :options="[
                { label: 'User', value: 'user' },
                { label: 'Admin', value: 'admin' }
              ]"
              label="Role"
              outlined
              emit-value
              map-options
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Batal" no-caps v-close-popup />
          <q-btn
            color="primary"
            label="Simpan"
            no-caps
            unelevated
            :loading="isSaving"
            @click="saveUser"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { adminAPI } from '../../services/api'
import { useAuthStore } from '../../stores/auth'
import { Dialog, Notify } from 'quasar'

const authStore = useAuthStore()

const users = ref([])
const isLoading = ref(true)
const search = ref('')
const filterRole = ref(null)
const showEditDialog = ref(false)
const editingUser = ref(null)
const isSaving = ref(false)

const editForm = reactive({
  username: '',
  email: '',
  role: 'user'
})

const columns = [
  { name: 'avatar', label: '', field: 'avatar', align: 'center', style: 'width: 60px' },
  { name: 'username', label: 'Username', field: 'username', align: 'left', sortable: true },
  { name: 'email', label: 'Email', field: 'email', align: 'left', sortable: true },
  { name: 'role', label: 'Role', field: 'role', align: 'center' },
  { name: 'created_at', label: 'Bergabung', field: 'created_at', align: 'left', sortable: true },
  { name: 'actions', label: 'Aksi', field: 'actions', align: 'center' }
]

const roleOptions = [
  { label: 'Semua', value: null },
  { label: 'User', value: 'user' },
  { label: 'Admin', value: 'admin' }
]

const filteredUsers = computed(() => {
  let result = users.value
  
  if (filterRole.value) {
    result = result.filter(u => u.role === filterRole.value)
  }
  
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    result = result.filter(u => 
      u.username?.toLowerCase().includes(searchLower) ||
      u.email?.toLowerCase().includes(searchLower)
    )
  }
  
  return result
})

// Format date
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

// Open edit dialog
const openEditDialog = (user) => {
  editingUser.value = user
  Object.assign(editForm, {
    username: user.username,
    email: user.email,
    role: user.role
  })
  showEditDialog.value = true
}

// Save user
const saveUser = async () => {
  if (!editForm.username || !editForm.email) return
  
  isSaving.value = true
  
  try {
    await adminAPI.updateUser(editingUser.value.id, { ...editForm })
    
    // Update local state
    const index = users.value.findIndex(u => u.id === editingUser.value.id)
    if (index !== -1) {
      users.value[index] = { ...users.value[index], ...editForm }
    }
    
    Notify.create({
      type: 'positive',
      message: 'Pengguna berhasil diperbarui',
      icon: 'check_circle'
    })
    
    showEditDialog.value = false
  } catch (err) {
    Notify.create({
      type: 'negative',
      message: 'Gagal memperbarui pengguna',
      icon: 'error'
    })
  } finally {
    isSaving.value = false
  }
}

// Confirm delete
const confirmDelete = (user) => {
  Dialog.create({
    title: 'Hapus Pengguna',
    message: `Apakah Anda yakin ingin menghapus "${user.username}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await adminAPI.deleteUser(user.id)
      users.value = users.value.filter(u => u.id !== user.id)
      
      Notify.create({
        type: 'positive',
        message: 'Pengguna berhasil dihapus',
        icon: 'check_circle'
      })
    } catch (err) {
      Notify.create({
        type: 'negative',
        message: 'Gagal menghapus pengguna',
        icon: 'error'
      })
    }
  })
}

// Fetch users
const fetchUsers = async () => {
  isLoading.value = true
  try {
    const response = await adminAPI.getUsers()
    if (response.data.status === 200) {
      users.value = response.data.data || []
    }
  } catch (err) {
    console.error('Failed to fetch users:', err)
    // Mock data for demo
    users.value = [
      { id: 1, username: 'admin', email: 'admin@optic.com', role: 'admin', created_at: '2024-01-01' },
      { id: 2, username: 'user1', email: 'user1@optic.com', role: 'user', created_at: '2024-01-15' },
      { id: 3, username: 'user2', email: 'user2@optic.com', role: 'user', created_at: '2024-02-01' }
    ]
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<style lang="scss" scoped>
.admin-users {
  background: #f8fafc;
  min-height: 100vh;
  padding: 24px;
}

.users-container {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 24px;
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

.users-card {
  border-radius: 16px;
  background: white;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.table-controls {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}

.search-input {
  flex: 1;
  max-width: 300px;
}

.filter-select {
  width: 150px;
}

.edit-dialog {
  width: 100%;
  max-width: 450px;
  border-radius: 16px;
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  
  h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a1a2e;
  }
}

// Responsive
@media (max-width: 767px) {
  .admin-users {
    padding: 16px;
  }
  
  .table-controls {
    flex-direction: column;
  }
  
  .search-input,
  .filter-select {
    width: 100%;
    max-width: none;
  }
}
</style>
