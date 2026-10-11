# Tugas Rutin 11 — E-Commerce DB + Secure Auth (Laravel)

Paket ini berisi **file tambahan** (migration, model, factory, seeder, middleware, policy,
controller, route, view) yang ditempel ke project Laravel hasil `composer create-project`
+ Laravel Breeze. Bentuknya sengaja begini karena folder `vendor/` (isi framework) selalu
di-download Composer di komputer Anda sendiri dan tidak pernah ikut di-push ke Git.

Urutan baca: **Bagian 1** (gambaran) → **Bagian 2** (langkah dari nol sampai Bagian A selesai)
→ **Bagian 3** (Bagian B: auth & keamanan) → **Bagian 4** (bonus) → **Bagian 5** (checklist & Git).

> Lingkungan yang diasumsikan: **Laragon** (buka lewat tombol *Terminal* bawaannya) + **MySQL**,
> database lewat phpMyAdmin. Semua perintah `php artisan ...` dijalankan di folder project.

---

## 1. Struktur Folder & Database

### 1.1 Isi paket ini → ditempel ke mana

```
laravel-p11/
├── database/
│   ├── migrations/   01_ … 07_ (7 file; isinya ditempel ke file hasil artisan, lihat 2.3)
│   ├── factories/    Category, Product, Cart, CartItem, Order, OrderItem
│   └── seeders/      RoleUser, Category, Product, OrderDemo, DatabaseSeeder
├── app/
│   ├── Models/       User (GABUNGKAN), Category, Product, Cart, CartItem, Order, OrderItem
│   ├── Http/Controllers/  ProductController, AdminController, EagerLoadingDemoController
│   ├── Http/Middleware/   CheckRole
│   └── Policies/     ProductPolicy
├── routes/           TAMBAHKAN_KE_web.php.txt   (TAMBAHAN, bukan pengganti web.php)
└── resources/views/  layouts/shop, products/{index,show,edit}, admin/index
```

### 1.2 Skema ringkas 7 tabel e-commerce

| # | Tabel | Kolom penting | Foreign Key |
|---|-------|---------------|-------------|
| 1 | `users` | id, name, email, password, **role** (admin/editor/user) | – |
| 2 | `categories` | id, nama, slug (unik) | – |
| 3 | `products` | id, category_id, nama, slug, deskripsi, harga, stok, gambar | `category_id` → categories (CASCADE) |
| 4 | `carts` | id, user_id (unik: 1 user = 1 cart) | `user_id` → users (CASCADE) |
| 5 | `cart_items` | id, cart_id, product_id, qty — unik (cart_id, product_id) | `cart_id` → carts, `product_id` → products (CASCADE) |
| 6 | `orders` | id, user_id, status, total, alamat_pengiriman | `user_id` → users (CASCADE) |
| 7 | `order_items` | id, order_id, product_id, qty, harga_saat_beli | `order_id` → orders (CASCADE), `product_id` → products (**RESTRICT**) |

```
users ─1:1─ carts  ─1:N─ cart_items  ─N:1─ products ─N:1─ categories
users ─1:N─ orders ─1:N─ order_items ─N:1─ products
```

Tabel lain di phpMyAdmin (`sessions`, `cache`, `jobs`, `migrations`, `password_reset_tokens`) adalah
tabel infrastruktur bawaan Laravel, **bukan** bagian dari 7 tabel e-commerce.

Keputusan desain yang layak disebut saat presentasi:
- **`harga_saat_beli`** di `order_items` = snapshot harga. Harga produk boleh berubah, riwayat order tidak.
- **`RESTRICT`** di `order_items.product_id` → produk yang punya riwayat order tidak bisa terhapus.
  `cart_items` memakai CASCADE karena cart hanya data sementara.
- **`unique(cart_id, product_id)`** → produk yang sama tidak muncul dua kali di satu cart (qty yang ditambah).

### 1.3 Relasi Eloquent

| Model | Relasi | Jenis |
|-------|--------|-------|
| `Category` | `products()` hasMany Product | One-to-Many |
| `Product` | `category()` belongsTo | Many-to-One |
| `Product` | `carts()` belongsToMany via `cart_items` (+pivot `qty`) | **Many-to-Many** |
| `Product` | `orders()` belongsToMany via `order_items` (+pivot `qty`, `harga_saat_beli`) | **Many-to-Many** |
| `User` | `cart()` hasOne, `orders()` hasMany | One-to-One, One-to-Many |
| `Cart` / `Order` | `user()` belongsTo, `items()` hasMany, `products()` belongsToMany | – |
| `CartItem` / `OrderItem` | `cart()`/`order()` + `product()` belongsTo | model untuk tabel penghubung |

**Local scope (syarat minimal 1) — ada 2, di `Product`:**
`scopeInStock()` (stok > 0) dan `scopePopular()` (urut berdasarkan jumlah baris `order_items`).

---

## 2. Bagian A — Database & Eloquent

### 2.1 Buat project, database, dan `.env`

```bash
composer create-project laravel/laravel ecommerce-auth
cd ecommerce-auth
```

1. Pasang phpMyAdmin di Laragon bila belum ada (klik kanan ikon Laragon → Tools → Quick add → phpmyadmin).
2. Buat database baru bernama `ecommerce_db` (collation `utf8mb4_unicode_ci`).
3. Ubah `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ecommerce_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### 2.2 Install Breeze dulu (agar tabel `users` & auth sudah ada)

Lihat **3.1** — Breeze diinstal sebelum membuat migration role. Setelah Breeze selesai
dan `php artisan migrate` berhasil, kembali ke 2.3.

### 2.3 Buat file memakai Artisan — **urutan ini penting**

Migration dijalankan berurutan menurut timestamp di nama file, sedangkan foreign key mengharuskan
tabel induk dibuat lebih dulu. Karena `make:migration` / `make:model -m` memberi timestamp saat perintah
dijalankan, **jalankan satu per satu dengan urutan ini** (tunggu tiap perintah selesai):

```bash
php artisan make:migration add_role_to_users_table --table=users   # -> paket: 01_
php artisan make:model Category  -mf                               # -> paket: 02_
php artisan make:model Product   -mf                               # -> paket: 03_
php artisan make:model Cart      -mf                               # -> paket: 04_
php artisan make:model CartItem  -mf                               # -> paket: 05_
php artisan make:model Order     -mf                               # -> paket: 06_
php artisan make:model OrderItem -mf                               # -> paket: 07_

php artisan make:seeder RoleUserSeeder
php artisan make:seeder CategorySeeder
php artisan make:seeder ProductSeeder
php artisan make:seeder OrderDemoSeeder
```

Lalu cek `database/migrations/`: urutan nama file harus
`add_role_to_users` → `categories` → `products` → `carts` → `cart_items` → `orders` → `order_items`.
Kalau ada dua file yang timestamp-nya **sama persis** (dibuat dalam detik yang sama) dan urutannya
terbalik, ganti angka detik pada nama file agar urutannya benar.

### 2.4 Tempel isi file dari paket

Nama file di project Anda **tidak perlu sama** dengan nama di paket (terutama migration, karena ada timestamp);
yang ditempel adalah **isinya**.

| Dari paket | Tempel ke (di project Anda) |
|------------|-----------------------------|
| `database/migrations/01_…` s.d. `07_…` | isi file migration hasil 2.3, sesuai nama tabelnya |
| `app/Models/Category, Product, Cart, CartItem, Order, OrderItem.php` | timpa isi file model hasil 2.3 |
| `app/Models/User.php` | **gabungkan**, jangan timpa buta-buta (lihat catatan di bawah) |
| `database/factories/*.php` | timpa isi file factory hasil 2.3 |
| `database/seeders/*.php` | timpa isi seeder hasil 2.3 + `DatabaseSeeder.php` bawaan |

**Catatan `User.php`:** file bawaan Breeze/Laravel Anda tetap dipertahankan. Tambahkan saja
(1) `use` untuk `HasMany`/`HasOne`, (2) method `cart()` & `orders()`, (3) tiga helper `isAdmin()/isEditor()/isUser()`.
`role` **sengaja tidak** dimasukkan ke `$fillable` — supaya kode ceroboh seperti `User::create($request->all())`
tidak bisa dipakai orang untuk mendaftar sebagai admin. Factory/seeder tetap bisa mengisi `role` karena
Eloquent Factory otomatis mematikan proteksi mass assignment.

### 2.5 Jalankan migration + seeder

```bash
php artisan migrate:fresh --seed
```

Hasil: 7 tabel e-commerce terbentuk, **64 produk** (8 per kategori, 5 di antaranya stok habis), 8 kategori,
3 akun role tetap + 5 user acak, 1 cart, 15 order beserta item-nya.

Nama produk disusun dari daftar jenis produk per kategori (`ProductFactory::$katalog`), jadi "Laptop" selalu
ada di Elektronik dan "Pulpen" selalu ada di Buku & Alat Tulis. Hanya varian di belakang nama (Pro, Hemat, dan lain-lain)
dan harga di dalam rentang jenisnya yang acak.

Akun untuk pengujian (password semuanya `password`):

| Role | Email |
|------|-------|
| admin | `admin@ecommerce.test` |
| editor | `editor@ecommerce.test` |
| user | `user@ecommerce.test` |

> Verifikasi skema: bentuk tabel, foreign key, UNIQUE, CASCADE, dan RESTRICT pada ketujuh migration
> sudah diuji pada MySQL/MariaDB (padanan DDL-nya). Anda bisa melihat relasinya di phpMyAdmin:
> buka database → tab **Designer**. Screenshot ini cocok untuk dokumentasi tugas.

### 2.6 Dokumentasi 5 query Tinker

Jalankan `php artisan tinker`, ketik query satu per satu, lalu **screenshot tiap hasilnya**.
Angka/ nama bisa berbeda karena data Faker acak — yang dinilai adalah query dan penjelasannya.

**Query 1 — `where` + memilih kolom tertentu**
```php
User::where('role', 'admin')->get(['id', 'name', 'email', 'role']);
```
Mengambil semua user ber-role admin dan hanya menampilkan 4 kolom. Hasil yang diharapkan: 1 baris
(`Admin Utama`, `admin@ecommerce.test`). Ini dasar query Eloquent: `where()` menyaring baris, argumen `get([...])` memilih kolom.

**Query 2 — relasi One-to-Many lewat query builder relasi**
```php
$kategori = Category::where('slug', 'elektronik')->first();
$kategori->products()->count();
$kategori->products()->orderByDesc('harga')->take(3)->get(['nama', 'harga']);
```
`products()` (dengan kurung) mengembalikan *query builder* relasi, sehingga bisa ditambah `count()`, `orderByDesc()`, `take()`
dan semuanya dikerjakan di database. Berbeda dengan `$kategori->products` (tanpa kurung) yang langsung memuat **semua** produk ke memori.
Hasil: jumlah produk kategori Elektronik (8 produk) dan 3 produk termahalnya (Laptop pasti yang termahal).

**Query 3 — local scope `inStock()`**
```php
Product::count();
Product::inStock()->count();
Product::inStock()->where('stok', 0)->count();
```
Baris pertama: total produk (64). Baris kedua: hanya yang stoknya > 0 — angkanya lebih kecil
(minimal 5 lebih sedikit, karena seeder sengaja mengosongkan stok 5 produk). Baris ketiga membuktikan scope bekerja: hasilnya **0**.
Scope membungkus kondisi `where('stok', '>', 0)` supaya bisa dipakai ulang dan dirangkai dengan query lain.

**Query 4 — Many-to-Many dengan data pivot (isi keranjang)**
```php
$user = User::where('email', 'user@ecommerce.test')->first();
$user->cart->products->map(fn ($p) => [$p->nama, $p->pivot->qty]);
```
Dari user ke cart (One-to-One), lalu ke produk di dalamnya lewat tabel penghubung `cart_items` (Many-to-Many).
`$p->pivot->qty` membaca kolom `qty` dari tabel penghubung, bukan dari tabel `products`.
Hasil: 3 produk beserta jumlahnya.

**Query 5 — scope `popular()` + `withCount`**
```php
Product::popular()->take(5)->get()->map(fn ($p) => [$p->nama, $p->order_items_count]);
```
`popular()` memakai `withCount('orderItems')` sehingga tiap produk punya atribut `order_items_count`
(jumlah baris order_items yang memuatnya), lalu diurutkan menurun. Hasil: 5 produk yang paling sering dipesan.
Menghitung jumlah relasi di database seperti ini jauh lebih efisien daripada memuat semua relasinya lalu `count()` di PHP.

---

## 3. Bagian B — Auth & Security

### 3.1 Install Laravel Breeze (login, register, logout)

Installer Laravel versi baru tidak lagi menawarkan Breeze sebagai starter kit, tetapi paketnya masih
dipelihara dan dipasang manual:

```bash
composer require laravel/breeze --dev
php artisan breeze:install        # pilih stack: Blade (with Alpine); dark mode: bebas; testing: bebas
php artisan migrate
npm install
npm run dev                        # biarkan jalan di terminal terpisah (Tailwind/Vite)
```

Bila `npm` tidak dikenali, pakai Terminal bawaan Laragon edisi **Full** (Node.js sudah termasuk).
Setelah ini `/login`, `/register`, dan tombol logout sudah berfungsi (requirement #5).

### 3.2 Multi-role

- Kolom `role` ditambahkan oleh migration `01_add_role_to_users_table` (enum `admin|editor|user`, default `user`).
- User yang mendaftar lewat Breeze otomatis berperan **user**, karena form register tidak mengirim `role`
  dan kolom itu memiliki default `user`.
- Akun admin & editor dibuat oleh `RoleUserSeeder`.

### 3.3 Custom middleware `CheckRole`

```bash
php artisan make:middleware CheckRole
```
Tempel isi `app/Http/Middleware/CheckRole.php` dari paket. Lalu **daftarkan alias `role`** di
`bootstrap/app.php` (Laravel 11+ tidak lagi memakai `Kernel.php`). Tambahkan di dalam `withMiddleware`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```
(Bila closure `withMiddleware` Anda sudah berisi baris lain dari Breeze, biarkan dan **tambahkan** blok `alias` di dalamnya.)
Pemakaian di route: `->middleware('role:admin')` atau `->middleware('role:admin,editor')`.

### 3.4 Policy untuk otorisasi edit/delete

```bash
php artisan make:policy ProductPolicy --model=Product
```
Tempel isi `app/Policies/ProductPolicy.php` dari paket. (Ketentuan menyebut "PostPolicy atau ProductPolicy";
dipakai **ProductPolicy** karena model di tugas ini adalah Product.) Tidak perlu didaftarkan manual —
Laravel mengenalinya otomatis lewat konvensi nama `{Model}Policy`.

| Aksi | admin | editor | user |
|------|:-----:|:------:|:----:|
| `update` (edit produk) | ✔ | ✔ | ✘ |
| `delete` (hapus produk) | ✔ | ✘ | ✘ |

Policy dipanggil di `ProductController` lewat `Gate::authorize('update', $product)` /
`Gate::authorize('delete', $product)`. Sejak Laravel 11 class `Controller` dasar tidak lagi menyertakan trait
`AuthorizesRequests`, jadi `$this->authorize()` versi lama tidak otomatis tersedia; pemanggilan lewat facade
`Gate` adalah cara yang berlaku sekarang. Di Blade, `@can('update', $product)` dipakai untuk menyembunyikan tombol.

**Middleware vs Policy — keduanya dipakai dengan sengaja:**
middleware `role` menjawab *"golongan ini boleh masuk area ini?"* (per halaman), sedangkan Policy menjawab
*"boleh melakukan aksi ini pada produk ini?"* (per aksi). Dua lapis = *defense in depth*.

### 3.5 Controller, route, dan view

```bash
php artisan make:controller ProductController
php artisan make:controller AdminController
php artisan make:controller EagerLoadingDemoController --invokable
```
1. Tempel isi ketiga controller dari paket.
2. **Route:** buka `routes/TAMBAHKAN_KE_web.php.txt` dan ikuti petunjuk di dalamnya — **tambahkan** 3 baris `use`
   di atas `routes/web.php` Anda dan tempel blok route di paling bawah. **Jangan menimpa** `web.php`, karena
   route `/profile` bawaan Breeze akan hilang dan menu Breeze menjadi error.
3. **View:** salin folder `layouts/`, `products/`, `admin/` dari paket ke `resources/views/`.
   Layout `layouts/shop.blade.php` dibuat terpisah dari `<x-app-layout>` Breeze karena halaman produk harus
   bisa dibuka tamu yang belum login.

Peta route (cek dengan `php artisan route:list --path=products` dan `--path=admin` — bagus untuk screenshot):

| URL | Siapa yang boleh | Penjaga |
|-----|------------------|---------|
| `GET /products`, `GET /products/{id}` | semua orang (tamu juga) | – |
| `GET /products/{id}/edit`, `PUT /products/{id}` | admin, editor | `auth` + `role:admin,editor` + Policy `update` |
| `DELETE /products/{id}` | admin | `auth` + `role:admin` + Policy `delete` |
| `GET /admin`, `GET /admin/demo/eager-loading` | admin | `auth` + `role:admin` |

### 3.6 Testing di browser — mode incognito, 2 role berbeda

Siapkan: `php artisan migrate:fresh --seed`, `php artisan serve` (atau domain `.test` Laragon), dan `npm run dev` menyala.
Jendela **A** = browser biasa (admin). Jendela **B** = incognito (editor). Sesi keduanya terpisah, jadi bisa login bersamaan.

| # | Jendela | Akun | Langkah | Hasil yang diharapkan |
|---|---------|------|---------|------------------------|
| 1 | A | (belum login) | buka `/products` | daftar produk tampil |
| 2 | A | (belum login) | buka `/products/1/edit` | diarahkan ke `/login` |
| 3 | A | admin | login, buka salah satu produk | tombol **Edit** dan **Hapus** tampil |
| 4 | A | admin | buka `/admin` | halaman ringkasan admin tampil |
| 5 | B | editor | login, buka produk yang sama | hanya tombol **Edit**, tanpa **Hapus** |
| 6 | B | editor | Edit → ubah harga → Simpan | berhasil, muncul pesan sukses |
| 7 | B | editor | buka `/admin` | **403** (ditolak middleware `role:admin`) |
| 8 | B | editor | buktikan server menolak DELETE (lihat di bawah) | **403** |
| 9 | A | admin | hapus produk **tanpa** riwayat order | berhasil |
| 10 | A | admin | hapus produk yang **punya** riwayat order | ditolak dengan pesan ramah (aturan RESTRICT) |
| 11 | (opsional) | user | login, buka `/products/1/edit` | **403** |

**Langkah 8 (bukti penolakan di sisi server).** Tombol Hapus disembunyikan untuk editor, tetapi menyembunyikan
tombol bukan pengamanan. Di jendela B, buka DevTools → tab *Console*, jalankan (ganti `1` dengan id produk yang ada):

```js
fetch('/products/1', {
  method: 'DELETE',
  headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
}).then(r => console.log(r.status));
```
Hasil harus `403`.

**Mencari produk untuk langkah 9 & 10** (di Tinker):
```php
Product::doesntHave('orderItems')->first();   // belum pernah dipesan -> bisa dihapus (langkah 9)
Product::has('orderItems')->first();          // sudah punya riwayat order -> ditolak (langkah 10)
```
Screenshot yang perlu diambil: jendela A dan B berdampingan, halaman 403, dan hasil `route:list`.

---

## 4. Bonus

### 4.1 Integrasi Filament Admin Panel

Filament adalah paket Composer terpisah. Dari hasil pencarian saya, rilis terbarunya saat ini v5.x (rilis Juli 2026) dan membutuhkan Laravel 11+ serta PHP 8.2+.
Jangan mengunci versi — biarkan Composer memilih yang cocok dengan Laravel Anda:

```bash
composer require filament/filament
php artisan filament:install --panels
php artisan make:filament-user
php artisan make:filament-resource Product --generate
```
Secara bawaan panel Filament berada di `/admin`. **Awas bentrok:** route `/admin` milik `AdminController` di tugas ini
memakai path yang sama. Saat `filament:install` menanyakan ID panel, isi `backoffice` (jadi panel ada di `/backoffice`),
atau ubah prefix route ringkasan admin kita.

`--generate` membuat form dan tabel Resource otomatis dari kolom database `products`.
**Samakan hak aksesnya dengan sistem role kita** — di `User.php`:

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    // ... isi lain tetap ...

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();   // hanya admin yang boleh masuk panel Filament
    }
}
```
Tanpa kontrak ini, Filament hanya mengizinkan semua user masuk di environment *local*; di server produksi akses ditolak.
Filament otomatis memakai `ProductPolicy` kita untuk tombol edit/hapus di Resource.

> Catatan jujur: nama perintah dan struktur folder hasil generate dapat sedikit berbeda antar versi Filament
> (v3/v4/v5). Bila ada perbedaan dengan yang tertulis di sini, ikuti dokumentasi resmi di filamentphp.com/docs.
> Bagian Filament ini tidak dapat saya jalankan langsung karena membutuhkan Composer.

### 4.2 Demo Eager Loading (mencegah N+1 query)

Masuk sebagai admin, buka `/admin/demo/eager-loading`. Halaman mengembalikan JSON yang membandingkan dua cara
mengambil data yang **sama** (10 order beserta nama pelanggan dan nama produknya):

| Cara | Kode | Jumlah query |
|------|------|--------------|
| A. Lazy loading (masalah N+1) | `Order::latest('id')->take(10)->get()` lalu akses `$order->user`, `$order->items`, `$item->product` di dalam loop | **puluhan** (kira-kira 30–60: 1 + 10 + 10 + jumlah item) |
| B. Eager loading | `Order::with(['user', 'items.product'])->latest('id')->take(10)->get()` | tepat **4** (orders, users, order_items, products) |

Field `data_sama` bernilai `true` untuk membuktikan hasilnya identik; yang berbeda hanya jumlah query.
Semakin banyak order, selisihnya makin besar, karena cara A tumbuh linear terhadap jumlah baris sedangkan cara B tetap.
Di Query 5 Tinker sebelumnya, `withCount` juga merupakan bentuk eager aggregation.

Tips tambahan (best practice): di `AppServiceProvider::boot()` tambahkan
`Model::preventLazyLoading(! app()->isProduction());` agar lazy loading melempar exception saat development.
**Matikan dulu saat menjalankan demo A**, karena demo A memang sengaja memakai lazy loading.

---

## 5. Checklist, Catatan Clean Code, dan Git

### 5.1 Checklist requirement

- [x] 1. Migrations 7 tabel + FK — `database/migrations/01–07`
- [x] 2. Seeders + factories, 50+ produk — 64 produk (8 per kategori) lewat `ProductSeeder`
- [x] 3. Model + relationships + minimal 1 scope — `Product::inStock()`, `Product::popular()`
- [x] 4. Dokumentasi 5 query Tinker — bagian 2.6 (tinggal screenshot)
- [x] 5. Breeze (login/register/logout) — bagian 3.1
- [x] 6. Multi-role + custom middleware — `CheckRole`, alias `role`
- [x] 7. Policy untuk edit/delete — `ProductPolicy` + `Gate::authorize`
- [x] 8. Route protection + testing incognito 2 role — bagian 3.5 & 3.6
- [x] Bonus: Filament (bagian 4.1), eager loading (bagian 4.2)

### 5.2 Catatan clean code & keamanan yang diterapkan

- `role` tidak ada di `$fillable` (cegah mass assignment untuk eskalasi hak akses).
- Otorisasi dicek di **titik yang mengubah data** (`update`/`destroy`), bukan hanya di halaman form.
- Dua lapis proteksi: middleware `role` + Policy; di Blade `@can` hanya untuk tampilan.
- `in_array(..., true)` (strict) pada pengecekan role; response 403 jelas lewat `abort()`/`Gate::authorize()`.
- Return type pada semua method, `casts()` method gaya Laravel 11+, `foreignId()->constrained()`.
- Validasi input lewat `$request->validate()`; pola Post-Redirect-Get dengan flash message.
- Eager loading `with('category')` pada daftar produk; `restrictOnDelete()` untuk menjaga riwayat order.

### 5.3 Push ke GitHub

```bash
git init
git add .
git commit -m "Tugas Rutin 11 - E-Commerce DB + Secure Auth"
git branch -M main
git remote add origin <link-repo-TugasWeb-P11-EcommerceAuth>
git push -u origin main
```
Nama repo **TugasWeb-P11-EcommerceAuth**. `vendor/`, `node_modules/`, dan `.env` otomatis tidak ikut (ada di `.gitignore` bawaan Laravel).
Letakkan screenshot dokumentasi di README repo Anda.

### 5.4 Kalau ada error

| Pesan | Penyebab umum & solusi |
|-------|------------------------|
| `1005 Can't create table … foreign key constraint is incorrectly formed` / `Base table … not found` saat migrate | Urutan file migration terbalik. Perbaiki timestamp nama file sesuai 2.3, lalu `php artisan migrate:fresh --seed` |
| `Target class [role] does not exist` | Alias `role` belum didaftarkan di `bootstrap/app.php` (3.3) |
| `Route [profile.edit] not defined` | `routes/web.php` tertimpa; kembalikan route bawaan Breeze lalu **tambahkan** blok kita saja |
| `Call to undefined method …::authorize()` | Memakai `$this->authorize()`; gunakan `Gate::authorize()` seperti di paket |
| Tampilan polos tanpa CSS | `npm run dev` belum berjalan (atau jalankan `npm run build`) |
| `Unable to locate a class or view for component`/`Vite manifest not found` | Sama: Vite belum dijalankan |
| Login selalu gagal | Pastikan sudah `migrate:fresh --seed`; password akun contoh: `password` |
# TugasWeb-P11-EcommerceAuth
