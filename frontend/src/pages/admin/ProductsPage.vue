<template>
  <q-page class="admin-products">
    <div class="products-container">
      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Kelola Produk</h1>
          <p class="page-subtitle">Tambah, edit, dan hapus produk</p>
        </div>
        <q-btn
          color="primary"
          label="Tambah Produk"
          icon="add"
          no-caps
          unelevated
          @click="openProductDialog()"
        />
      </div>

      <!-- Products Table -->
      <q-card class="products-card" flat>
        <q-card-section>
          <!-- Search and Filter -->
          <div class="table-controls">
            <q-input
              v-model="search"
              placeholder="Cari produk..."
              outlined
              dense
              class="search-input"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
            
            <q-select
              v-model="filterCategory"
              :options="categoryOptions"
              label="Kategori"
              outlined
              dense
              emit-value
              map-options
              class="filter-select"
            />
          </div>

          <!-- Table -->
          <q-table
            :rows="filteredProducts"
            :columns="columns"
            row-key="id"
            flat
            :loading="productStore.isLoading"
            :pagination="{ rowsPerPage: 10 }"
          >
            <template v-slot:body-cell-image="props">
              <q-td :props="props">
                <q-img
                  :src="props.row.image_url"
                  class="product-thumb"
                  fit="contain"
                >
                  <template v-slot:error>
                    <div class="absolute-full flex flex-center bg-grey-2">
                      <q-icon name="broken_image" size="20px" color="grey-5" />
                    </div>
                  </template>
                </q-img>
              </q-td>
            </template>

            <template v-slot:body-cell-category="props">
              <q-td :props="props">
                <q-badge :color="getCategoryColor(props.row.category_id)">
                  {{ productStore.getCategoryName(props.row.category_id) }}
                </q-badge>
              </q-td>
            </template>

            <template v-slot:body-cell-price="props">
              <q-td :props="props">
                <span class="text-weight-bold text-accent">
                  {{ productStore.formatPrice(props.value) }}
                </span>
              </q-td>
            </template>

            <template v-slot:body-cell-stock="props">
              <q-td :props="props">
                <q-badge 
                  :color="props.value < 10 ? (props.value === 0 ? 'negative' : 'warning') : 'positive'"
                >
                  {{ props.value }}
                </q-badge>
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
                  @click="openProductDialog(props.row)"
                />
                <q-btn
                  flat
                  round
                  dense
                  icon="delete"
                  color="negative"
                  @click="confirmDelete(props.row)"
                />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <!-- Product Dialog -->
    <q-dialog v-model="showDialog" persistent>
      <q-card class="product-dialog">
        <q-card-section class="dialog-header">
          <h3>{{ isEditing ? 'Edit Produk' : 'Tambah Produk' }}</h3>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit.prevent="saveProduct" class="q-gutter-md">
            <q-input
              v-model="form.name"
              label="Nama Produk"
              outlined
              :rules="[val => !!val || 'Nama produk wajib diisi']"
            />

            <q-select
              v-model="form.category_id"
              :options="productStore.categories"
              label="Kategori"
              outlined
              emit-value
              map-options
              option-value="id"
              option-label="name"
              :rules="[val => !!val || 'Kategori wajib dipilih']"
            />

            <q-input
              v-model="form.description"
              label="Deskripsi"
              type="textarea"
              outlined
              rows="3"
            />

            <div class="row q-col-gutter-md">
              <div class="col-6">
                <q-input
                  v-model.number="form.price"
                  label="Harga (IDR)"
                  type="number"
                  outlined
                  :rules="[val => val >= 0 || 'Harga tidak valid']"
                />
              </div>
              <div class="col-6">
                <q-input
                  v-model.number="form.stock"
                  label="Stok"
                  type="number"
                  outlined
                  :rules="[val => val >= 0 || 'Stok tidak valid']"
                />
              </div>
            </div>

            <q-input
              v-model="form.image_url"
              label="URL Gambar"
              outlined
              hint="Masukkan URL gambar produk"
            />

            <!-- Image Preview -->
            <div v-if="form.image_url" class="image-preview">
              <q-img
                :src="form.image_url"
                fit="contain"
                style="height: 150px"
              >
                <template v-slot:error>
                  <div class="absolute-full flex flex-center bg-grey-2">
                    <span class="text-grey-5">Gambar tidak valid</span>
                  </div>
                </template>
              </q-img>
            </div>
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Batal" no-caps v-close-popup />
          <q-btn
            color="primary"
            :label="isEditing ? 'Simpan' : 'Tambah'"
            no-caps
            unelevated
            :loading="isSaving"
            @click="saveProduct"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useProductStore } from '../../stores/product'
import { Dialog } from 'quasar'

const productStore = useProductStore()

const search = ref('')
const filterCategory = ref(null)
const showDialog = ref(false)
const isEditing = ref(false)
const isSaving = ref(false)
const editingId = ref(null)

const form = reactive({
  name: '',
  category_id: '',
  description: '',
  price: 0,
  stock: 0,
  image_url: ''
})

const columns = [
  { name: 'image', label: 'Gambar', field: 'image_url', align: 'center', style: 'width: 80px' },
  { name: 'name', label: 'Nama Produk', field: 'name', align: 'left', sortable: true },
  { name: 'category', label: 'Kategori', field: 'category_id', align: 'center' },
  { name: 'price', label: 'Harga', field: 'price', align: 'right', sortable: true },
  { name: 'stock', label: 'Stok', field: 'stock', align: 'center', sortable: true },
  { name: 'actions', label: 'Aksi', field: 'actions', align: 'center' }
]

const categoryOptions = computed(() => [
  { label: 'Semua', value: null },
  ...productStore.categories.map(c => ({ label: c.name, value: c.id }))
])

const filteredProducts = computed(() => {
  let products = productStore.products
  
  if (filterCategory.value) {
    products = products.filter(p => p.category_id === filterCategory.value)
  }
  
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    products = products.filter(p => 
      p.name.toLowerCase().includes(searchLower) ||
      p.description?.toLowerCase().includes(searchLower)
    )
  }
  
  return products
})

const getCategoryColor = (categoryId) => {
  const colors = {
    '1': 'amber-9',
    '2': 'blue-8',
    '3': 'teal-7'
  }
  return colors[categoryId] || 'grey-7'
}

const openProductDialog = (product = null) => {
  if (product) {
    isEditing.value = true
    editingId.value = product.id
    Object.assign(form, {
      name: product.name,
      category_id: product.category_id,
      description: product.description,
      price: product.price,
      stock: product.stock,
      image_url: product.image_url
    })
  } else {
    isEditing.value = false
    editingId.value = null
    Object.assign(form, {
      name: '',
      category_id: '',
      description: '',
      price: 0,
      stock: 0,
      image_url: ''
    })
  }
  showDialog.value = true
}

const saveProduct = async () => {
  if (!form.name || !form.category_id) return
  
  isSaving.value = true
  
  if (isEditing.value) {
    await productStore.updateProduct(editingId.value, { ...form })
  } else {
    await productStore.createProduct({ ...form })
  }
  
  isSaving.value = false
  showDialog.value = false
}

const confirmDelete = (product) => {
  Dialog.create({
    title: 'Hapus Produk',
    message: `Apakah Anda yakin ingin menghapus "${product.name}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    await productStore.deleteProduct(product.id)
  })
}

onMounted(() => {
  productStore.fetchProducts()
})
</script>

<style lang="scss" scoped>
.admin-products {
  background: #f8fafc;
  min-height: 100vh;
  padding: 24px;
}

.products-container {
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.products-card {
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
  width: 200px;
}

.product-thumb {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  background: #f8fafc;
}

.product-dialog {
  width: 100%;
  max-width: 500px;
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

.image-preview {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  background: #f8fafc;
}

// Responsive
@media (max-width: 767px) {
  .admin-products {
    padding: 16px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
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
