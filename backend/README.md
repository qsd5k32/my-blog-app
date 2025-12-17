# Blog API - Laravel 11 Backend

A production-ready RESTful API for a blog application built with Laravel 11. Features include user authentication, post management, and a comment system.

## Features

- 🔐 **User Authentication** - Token-based authentication using Laravel Sanctum
- 📝 **Post Management** - Create, read, update, and delete blog posts
- 💬 **Comment System** - Users can comment on posts
- 🔒 **Authorization** - Users can only edit/delete their own posts and comments
- ✅ **Input Validation** - Comprehensive validation for all endpoints
- 📊 **Pagination** - All list endpoints support pagination
- 🛡️ **Security** - Protection against SQL injection, XSS, and CSRF attacks
- 🎯 **RESTful Design** - Clean and consistent API design

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL 8.0+ or PostgreSQL 13+
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- BCMath PHP Extension

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd blog-api
```

### 2. Install dependencies

```bash
composer install
```

### 3. Environment configuration

Copy the example environment file and configure your database:

```bash
cp .env.example .env
```

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog_api
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Generate application key

```bash
php artisan key:generate
```

### 5. Run migrations

Create the database tables:

```bash
php artisan migrate
```

### 6. Start the development server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## API Documentation

### Base URL

```
http://localhost:8000/api
```

### Authentication

All endpoints except `/api/auth/register` and `/api/auth/login` require authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your_token}
```

### Endpoints

#### Authentication

**Register a new user**
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!"
}

Response: 201 Created
{
    "message": "User registered successfully",
    "user": {...},
    "access_token": "...",
    "token_type": "Bearer"
}
```

**Login**
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "SecurePass123!"
}

Response: 200 OK
{
    "message": "Login successful",
    "user": {...},
    "access_token": "...",
    "token_type": "Bearer"
}
```

**Logout**
```http
POST /api/auth/logout
Authorization: Bearer {token}

Response: 200 OK
{
    "message": "Logged out successfully"
}
```

#### Posts

**Get all posts**
```http
GET /api/posts?per_page=15&page=1&published=true
Authorization: Bearer {token}

Response: 200 OK
{
    "data": [...],
    "current_page": 1,
    "per_page": 15,
    "total": 100
}
```

**Get a single post**
```http
GET /api/posts/{id}
Authorization: Bearer {token}

Response: 200 OK
{
    "id": 1,
    "title": "...",
    "content": "...",
    "published": true,
    "user": {...},
    "comments": [...]
}
```

**Create a new post**
```http
POST /api/posts
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "My First Post",
    "content": "This is the content of my post...",
    "published": true
}

Response: 201 Created
{
    "message": "Post created successfully",
    "post": {...}
}
```

**Update a post**
```http
PUT /api/posts/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Updated Title",
    "content": "Updated content...",
    "published": true
}

Response: 200 OK
{
    "message": "Post updated successfully",
    "post": {...}
}
```

**Delete a post**
```http
DELETE /api/posts/{id}
Authorization: Bearer {token}

Response: 200 OK
{
    "message": "Post deleted successfully"
}
```

#### Comments

**Get comments for a post**
```http
GET /api/posts/{postId}/comments?per_page=15
Authorization: Bearer {token}

Response: 200 OK
{
    "data": [...],
    "current_page": 1,
    "per_page": 15
}
```

**Create a comment**
```http
POST /api/posts/{postId}/comments
Authorization: Bearer {token}
Content-Type: application/json

{
    "content": "Great post!"
}

Response: 201 Created
{
    "message": "Comment created successfully",
    "comment": {...}
}
```

**Update a comment**
```http
PUT /api/comments/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "content": "Updated comment text"
}

Response: 200 OK
{
    "message": "Comment updated successfully",
    "comment": {...}
}
```

**Delete a comment**
```http
DELETE /api/comments/{id}
Authorization: Bearer {token}

Response: 200 OK
{
    "message": "Comment deleted successfully"
}
```

## Error Responses

The API returns appropriate HTTP status codes:

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

Error response format:
```json
{
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

## Database Schema

### Users Table
- `id` - Primary key
- `name` - User's full name
- `email` - Unique email address
- `password` - Hashed password
- `created_at`, `updated_at` - Timestamps

### Posts Table
- `id` - Primary key
- `title` - Post title
- `content` - Post content
- `user_id` - Foreign key to users
- `published` - Boolean (published status)
- `created_at`, `updated_at` - Timestamps

### Comments Table
- `id` - Primary key
- `post_id` - Foreign key to posts
- `user_id` - Foreign key to users
- `content` - Comment content
- `created_at`, `updated_at` - Timestamps

## Security Features

- **Password Hashing** - All passwords are hashed using bcrypt
- **Token Authentication** - Secure token-based authentication with Laravel Sanctum
- **SQL Injection Protection** - Using Eloquent ORM and prepared statements
- **XSS Protection** - Input sanitization and output escaping
- **CSRF Protection** - Built-in Laravel CSRF protection
- **Rate Limiting** - API rate limiting to prevent abuse
- **Authorization** - Users can only modify their own resources

## Testing

Run the test suite:

```bash
php artisan test
```

## Code Quality

Run PHP code style fixer:

```bash
./vendor/bin/pint
```

## Production Deployment

### 1. Environment Setup

Update your `.env` file for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Use strong, random keys
APP_KEY=base64:...

# Production database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password
```

### 2. Optimize for Production

```bash
# Install dependencies without dev packages
composer install --optimize-autoloader --no-dev

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Run migrations
php artisan migrate --force
```

### 3. Server Requirements

- Configure your web server (Nginx/Apache) to point to the `public` directory
- Set appropriate file permissions
- Enable HTTPS with SSL certificate
- Configure CORS if needed
- Set up queue workers if using queues
- Configure cron for scheduled tasks

### 4. Performance Optimization

- Enable OPcache
- Use Redis for caching and sessions
- Configure queue workers for background jobs
- Set up CDN for static assets
- Enable database query caching

## Support

For issues, questions, or contributions, please open an issue in the repository.

## License

This project is open-sourced software licensed under the MIT license.
