"Tolong buatkan code untuk fitur CRUD untuk bagian Produk dulu di CodeIgniter 4 dengan ketentuan sebagai berikut:

1. Arsitektur & Teknologi:

Database: PostgreSQL (buatkan juga file migrasinya).

PHP Version: 8.2 (gunakan declare(strict_types=1);).

Response: Gunakan CodeIgniter\API\ResponseTrait untuk hasil JSON yang konsisten.

2. Komponen yang Dibutuhkan:

Migration: Tabel categories (id, name) dan products (id, category_id, name, description, price, stock, image_url, created_at, updated_at).

Model: ProductModel dengan fitur Validation Rules di dalam properti model.

Controller: ProductController yang menangani:

index(): List produk dengan Join ke tabel kategori.

show($id): Detail satu produk.

create(): Validasi input dan simpan data (khusus Admin).

update($id): Update stok atau harga (khusus Admin).

delete($id): Hapus data (khusus Admin).

3. Standar Koding:

Gunakan Service Response atau format standar: { "status": 200, "message": "...", "data": [...] }.

Terapkan Try-Catch block untuk menangani error database.

Gunakan Query Builder CI4 untuk mencegah SQL Injection.

Berikan komentar singkat pada setiap fungsi untuk menjelaskan logikanya.

4. Routing:

Tuliskan kode untuk file Config/Routes.php menggunakan route-group agar rapi."
