# Pre-Workout Shop Frontend

Frontend Vue.js 3 dengan Quasar Framework untuk aplikasi Pre-Workout Shop.

## Tech Stack

- **Vue.js 3** - Framework JavaScript progresif
- **Quasar Framework 2** - UI Framework dengan Material Design
- **Pinia** - State management untuk Vue 3
- **Vue Router 4** - Routing SPA
- **Axios** - HTTP client untuk API calls

## Fitur

### User Features
- Register & Login dengan JWT Authentication
- Browse produk dengan filter kategori (tab)
- Keranjang belanja (Cart) dengan Pinia store
- Checkout dan konfirmasi pesanan
- Riwayat pesanan (Order History)
- Profile management

### Admin Features
- Dashboard dengan statistik (total users, orders, revenue)
- CRUD Produk (Create, Read, Update, Delete)
- Manajemen Order (update status: pending, paid, processing, shipped, completed, cancelled)
- Manajemen User

## Struktur Folder

```
frontend/
├── src/
│   ├── components/        # Reusable components
│   │   └── ProductCard.vue
│   ├── layouts/           # Layout wrappers
│   │   ├── MainLayout.vue
│   │   └── AdminLayout.vue
│   ├── pages/             # Route pages
│   │   ├── LoginPage.vue
│   │   ├── RegisterPage.vue
│   │   ├── ShopPage.vue
│   │   ├── CartPage.vue
│   │   ├── CheckoutPage.vue
│   │   ├── OrdersPage.vue
│   │   ├── ProfilePage.vue
│   │   └── admin/
│   │       ├── DashboardPage.vue
│   │       ├── ProductsPage.vue
│   │       ├── OrdersPage.vue
│   │       └── UsersPage.vue
│   ├── router/            # Vue Router configuration
│   ├── services/          # API services (Axios)
│   ├── stores/            # Pinia stores
│   │   ├── auth.js
│   │   ├── cart.js
│   │   └── product.js
│   └── css/               # Global styles
├── index.html
├── package.json
└── quasar.config.js
```

## Instalasi

### Prerequisites
- Node.js >= 18.x
- npm atau yarn
- Backend API running di http://localhost:8080

### Setup

1. Install dependencies:
```bash
cd frontend
npm install
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Edit `.env` sesuai konfigurasi backend:
```
VITE_API_URL=http://localhost:8080
```

4. Jalankan development server:
```bash
npm run dev
```

Aplikasi akan berjalan di `http://localhost:9000`

## Scripts

```bash
# Development
npm run dev

# Build untuk production
npm run build

# Preview production build
npm run preview

# Lint
npm run lint
```

## Koneksi dengan Backend

Frontend terhubung dengan backend CodeIgniter 4 melalui REST API:

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/register` | POST | Registrasi user baru |
| `/login` | POST | Login dan mendapat JWT |
| `/me` | GET | Info user yang login |
| `/products` | GET | List semua produk |
| `/products/:id` | GET | Detail produk |
| `/cart` | GET/POST/PUT/DELETE | Manajemen keranjang |
| `/checkout` | POST | Proses checkout |
| `/orders` | GET | Riwayat pesanan |
| `/orders/:id` | GET/PATCH | Detail & update pesanan |
| `/admin/*` | * | Endpoint admin |

## Environment Variables

| Variable | Deskripsi | Default |
|----------|-----------|---------|
| `VITE_API_URL` | Base URL backend API | `http://localhost:8080` |

## Notes

- JWT token disimpan di localStorage
- Token otomatis dikirim via Authorization header
- Session expired akan redirect ke login page
- Admin routes dilindungi oleh auth guard
