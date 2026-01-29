# Product CRUD API - CodeIgniter 4

This documentation describes the Product CRUD API built with CodeIgniter 4, PostgreSQL, and PHP 8.2.

## Features

- ✅ PHP 8.2 with strict typing (`declare(strict_types=1)`)
- ✅ PostgreSQL database with migrations
- ✅ CodeIgniter 4 with ResponseTrait for consistent JSON responses
- ✅ Complete CRUD operations for Products
- ✅ Join operations with Categories
- ✅ Input validation with Model validation rules
- ✅ Error handling with try-catch blocks
- ✅ SQL Injection protection using Query Builder
- ✅ RESTful API design

## Database Schema

### Categories Table

```sql
- id (SERIAL, PRIMARY KEY)
- name (VARCHAR(255), UNIQUE, NOT NULL)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### Products Table

```sql
- id (SERIAL, PRIMARY KEY)
- category_id (INT, FOREIGN KEY -> categories.id)
- name (VARCHAR(255), NOT NULL)
- description (TEXT, NULLABLE)
- price (DECIMAL(10,2), NOT NULL, DEFAULT 0.00)
- stock (INT, NOT NULL, DEFAULT 0)
- image_url (VARCHAR(500), NULLABLE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

## API Endpoints

Base URL: `/api/v1`

### 1. Get All Products

```
GET /api/v1/products
```

**Query Parameters:**

- `limit` (optional): Number of products to retrieve
- `offset` (optional): Number of products to skip (default: 0)

**Response:**

```json
{
  "status": 200,
  "message": "Products retrieved successfully",
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "name": "C4 Original Pre-Workout",
      "description": "The original pre-workout supplement",
      "price": "29.99",
      "stock": 100,
      "image_url": "https://example.com/c4-original.jpg",
      "created_at": "2024-01-29 10:00:00",
      "updated_at": "2024-01-29 10:00:00",
      "category_name": "Pre-Workout Supplements"
    }
  ]
}
```

### 2. Get Single Product

```
GET /api/v1/products/{id}
```

**Response:**

```json
{
  "status": 200,
  "message": "Product retrieved successfully",
  "data": {
    "id": 1,
    "category_id": 1,
    "name": "C4 Original Pre-Workout",
    "description": "The original pre-workout supplement",
    "price": "29.99",
    "stock": 100,
    "image_url": "https://example.com/c4-original.jpg",
    "created_at": "2024-01-29 10:00:00",
    "updated_at": "2024-01-29 10:00:00",
    "category_name": "Pre-Workout Supplements"
  }
}
```

### 3. Create Product (Admin Only)

```
POST /api/v1/products
```

**Request Body:**

```json
{
  "category_id": 1,
  "name": "C4 Original Pre-Workout",
  "description": "The original pre-workout supplement",
  "price": 29.99,
  "stock": 100,
  "image_url": "https://example.com/c4-original.jpg"
}
```

**Response:**

```json
{
  "status": 201,
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "category_id": 1,
    "name": "C4 Original Pre-Workout",
    "description": "The original pre-workout supplement",
    "price": "29.99",
    "stock": 100,
    "image_url": "https://example.com/c4-original.jpg",
    "created_at": "2024-01-29 10:00:00",
    "updated_at": "2024-01-29 10:00:00",
    "category_name": "Pre-Workout Supplements"
  }
}
```

### 4. Update Product (Admin Only)

```
PUT /api/v1/products/{id}
PATCH /api/v1/products/{id}
```

**Request Body (partial update supported):**

```json
{
  "stock": 150,
  "price": 34.99
}
```

**Response:**

```json
{
  "status": 200,
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    "category_id": 1,
    "name": "C4 Original Pre-Workout",
    "description": "The original pre-workout supplement",
    "price": "34.99",
    "stock": 150,
    "image_url": "https://example.com/c4-original.jpg",
    "created_at": "2024-01-29 10:00:00",
    "updated_at": "2024-01-29 11:00:00",
    "category_name": "Pre-Workout Supplements"
  }
}
```

### 5. Delete Product (Admin Only)

```
DELETE /api/v1/products/{id}
```

**Response:**

```json
{
  "status": 200,
  "message": "Product deleted successfully",
  "data": null
}
```

## Error Responses

### 400 Bad Request

```json
{
  "status": 400,
  "message": "Invalid product ID",
  "data": null
}
```

### 404 Not Found

```json
{
  "status": 404,
  "message": "Product not found",
  "data": null
}
```

### 422 Validation Error

```json
{
  "status": 422,
  "message": "Validation failed",
  "data": {
    "errors": {
      "name": "Product name is required",
      "price": "Price must be 0 or greater"
    }
  }
}
```

### 500 Internal Server Error

```json
{
  "status": 500,
  "message": "Internal server error",
  "data": null
}
```

## Validation Rules

### Product Creation/Update

- `category_id`: Required, integer, must exist in categories table
- `name`: Required, max 255 characters
- `description`: Optional, string
- `price`: Required, decimal, >= 0
- `stock`: Required, integer, >= 0
- `image_url`: Optional, max 500 characters, valid URL format

## Installation & Setup

1. **Clone the repository**
2. **Install dependencies:**

   ```bash
   composer install
   ```

3. **Setup database:**

   ```bash
   # Create PostgreSQL database
   createdb pre_workout_db

   # Run migrations
   php spark migrate

   # Run seeders (optional)
   php spark db:seed CategorySeeder
   ```

4. **Configure environment:**
   - Copy `.env.example` to `.env`
   - Update database credentials in `.env`

5. **Start the server:**
   ```bash
   php spark serve
   ```

## File Structure

```
app/
├── Controllers/
│   └── ProductController.php          # API endpoints handler
├── Models/
│   ├── ProductModel.php               # Product model with validation
│   └── CategoryModel.php              # Category model
├── Database/
│   ├── Migrations/
│   │   ├── 2024-01-29-100001_CreateCategories.php
│   │   └── 2024-01-29-100002_CreateProducts.php
│   └── Seeds/
│       └── CategorySeeder.php         # Initial category data
└── Config/
    ├── Routes.php                     # API route definitions
    └── Database.php                   # Database configuration
```

## Security Features

- ✅ SQL Injection protection via Query Builder
- ✅ Input validation with custom rules
- ✅ Error logging for debugging
- ✅ Consistent error responses
- 🔄 Admin authentication (TODO - implement based on your auth system)

## Future Enhancements

- [ ] Implement admin authentication middleware
- [ ] Add pagination metadata to responses
- [ ] Add search and filtering capabilities
- [ ] Implement product image upload
- [ ] Add product categories CRUD
- [ ] Add API rate limiting
- [ ] Add API documentation with Swagger/OpenAPI
