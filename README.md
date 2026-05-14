# Real Estate API - Laravel 12

RESTful API backend for the Cambodia Real Estate Website built with Laravel 12, featuring property listings, user authentication, favorites, and inquiries.

## Features

- 🏠 **Property Management** - CRUD operations for real estate listings
- 🔐 **Authentication** - Laravel Sanctum token-based auth
- ⭐ **Favorites** - Users can save favorite properties
- 📧 **Inquiries** - Contact property owners
- 🔍 **Advanced Filtering** - Search by type, price, location, bedrooms, area
- 📱 **RESTful API** - Clean, consistent API design
- 🗄️ **SQLite Database** - Lightweight development database

## Tech Stack

- Laravel 12.38
- Laravel Sanctum 4.2 (API Authentication)
- SQLite (Development)
- PHP 8.2+

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- SQLite extension enabled

### Setup Steps

1. **Install dependencies**
```bash
composer install
```

2. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Database setup**
```bash
php artisan migrate
php artisan db:seed
```

4. **Start the development server**
```bash
php artisan serve
# API available at: http://localhost:8000/api
```

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication

#### Register
```http
POST /api/register
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```
`name` is also accepted and will be split on the first space.

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "admin@login.com",
  "password": "Admin@123"
}
```

### Properties

#### Get All Properties (with filters)
```http
GET /api/properties?listing_type=For Sale&city=Phnom Penh&min_price=100000&max_price=500000&min_bedrooms=3

Query Parameters:
- listing_type: "For Sale" | "For Rent"
- property_type: "House" | "Apartment" | "Condo" | "Villa" | "Townhouse" | "Land" | "Commercial"
- city, district, min_price, max_price, min_bedrooms, bathrooms
- min_area, max_area, is_featured, search
- sort_by: "price" | "area" | "bedrooms" | "created_at" | "views"
- sort_order: "asc" | "desc"
- per_page: number (default: 15)
```

#### Get Featured Properties
```http
GET /api/properties/featured
```

#### Get Single Property
```http
GET /api/properties/{id}
```

#### Create Property (Protected)
```http
POST /api/properties
Authorization: Bearer {token}

{
  "title": "Beautiful Villa",
  "description": "Amazing property...",
  "listing_type": "For Sale",
  "property_type": "Villa",
  "price": 850000,
  "location": "BKK1, Phnom Penh",
  "city": "Phnom Penh",
  "bedrooms": 4,
  "bathrooms": 3,
  "area": 320
}
```

### Favorites (Protected)
```http
GET /api/favorites
POST /api/favorites { "property_id": 1 }
DELETE /api/favorites/{property_id}
```

### Inquiries
```http
POST /api/inquiries
{
  "property_id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com",
  "message": "I'm interested..."
}
```

## Sample Data

**Seeded accounts** (created by `CreateUserRolePermissionSeeder`):

| Role | Email | Password |
| --- | --- | --- |
| Super Admin | `superadmin@login.com` | `SuperAdmin@123` |
| Admin | `admin@login.com` | `Admin@123` |
| Manager | `manager@login.com` | `Manager@123` |
| Editor | `editor@login.com` | `Editor@123` |
| User 1–5 | `user1@login.com` … `user5@login.com` | `User@123` |

Sample data: 74 properties, 21 projects, 14 news articles, 75 settings.

## Frontend Integration (Next.js)

```javascript
// Example API call
const response = await fetch('http://localhost:8000/api/properties');
const data = await response.json();

// With authentication
const response = await fetch('http://localhost:8000/api/properties', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
```

## Development

```bash
# Refresh database
php artisan migrate:fresh --seed

# Create new controller
php artisan make:controller Api/NewController --api

# Create model with migration
php artisan make:model NewModel -m
```

## License

MIT License
# realestate-backend
# panha-realestate-api
# panha-realestate-api
