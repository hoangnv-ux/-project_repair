# Member Site

## I. Environment Setup

### 1. Install dependencies
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 2. Database configuration
```bash
# Edit .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=member_site
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Run migration and seeder
```bash
php artisan migrate
php artisan db:seed
```

### 4. Run server
```bash
php artisan serve
php artisan queue:work
```

### 5. Testing environment
```bash
php artisan key:generate --env=testing
php artisan jwt:secret --env=testing
```

---

## II. Project Structure

### Frontend
- **Template**: AdminLTE 3.x
- **View Engine**: Blade
- **Assets**: `public/adminlte/`

### Backend
- **Framework**: Laravel 11
- **Auth**: JWT (API) + Session (Admin)
- **Queue**: Redis/Database

### Important directories
```
app/
├── Http/
│   ├── Controllers/     # Controllers
│   └── Middleware/      # Middleware
├── Models/              # Models
resources/
├── views/
│   ├── layouts/         # Layout templates
│   │   ├── admin.blade.php
│   │   └── partials/
│   └── admin/           # Admin views
│       ├── auth/
│       └── dashboard.blade.php
routes/
├── web.php              # Web routes
└── api.php              # API routes
public/
└── adminlte/            # AdminLTE assets
```

---

## III. Routes

### Admin Routes
```
GET  /admin/login       -> Login page
GET  /admin/dashboard   -> Dashboard (auth:admin)
```

---

## IV. Common Commands

```bash
# Create controller
php artisan make:controller Admin/UserController

# Create model
php artisan make:model User -m

# Create migration
php artisan make:migration create_users_table

# Run migration
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Create seeder
php artisan make:seeder UserSeeder

# Run seeder
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## V. Documentation

- [AdminLTE 3 Docs](https://adminlte.io/docs/3.2/)
- [AdminLTE 3 Components](https://adminlte.io/themes/v3/)
- [Laravel Docs](https://laravel.com/docs)
- [JWT Auth](https://jwt-auth.readthedocs.io/)

---

## VI. Example

### Fulltext Search
```php
$keyword = 'tokyo';

$hotels = DB::table('hotels')
    ->whereRaw("MATCH(name, kana, tel, email) AGAINST (? IN NATURAL LANGUAGE MODE)", [$keyword])
    ->get();
```
