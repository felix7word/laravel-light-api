# Laravel Light API

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/felix7word/laravel-light-api/actions"><img src="https://github.com/felix7word/laravel-light-api/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://github.com/felix7word/laravel-light-api"><img src="https://img.shields.io/github/stars/felix7word/laravel-light-api" alt="GitHub Stars"></a>
<a href="https://github.com/felix7word/laravel-light-api"><img src="https://img.shields.io/github/forks/felix7word/laravel-light-api" alt="GitHub Forks"></a>
<a href="https://github.com/felix7word/laravel-light-api"><img src="https://img.shields.io/github/license/felix7word/laravel-light-api" alt="License"></a>
</p>

A lightweight Laravel API starter template for rapid development, designed to help you build RESTful APIs quickly and efficiently.

## Architecture

<p align="center">
  <img src="https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=Laravel%20API%20architecture%20diagram%20showing%20request%20flow%20from%20client%20to%20API%20routes%20to%20controllers%20to%20models%20and%20back%20to%20client%2C%20with%20authentication%20layer%20and%20database%2C%20professional%20technical%20diagram%20with%20Laravel%20logo%2C%20blue%20and%20gray%20color%20scheme&image_size=landscape_16_9" alt="Laravel API Architecture" width="800">
</p>

## Features

- ✅ Lightweight structure with minimal dependencies
- ✅ User authentication with Laravel Sanctum
- ✅ CRUD operations with example Post resource
- ✅ API documentation with Scribe
- ✅ Consistent API response format
- ✅ Advanced query filtering with Spatie Query Builder
- ✅ IDE helper support
- ✅ GitHub Actions workflow for code quality

## Getting Started

### Prerequisites

- PHP >= 8.1
- Composer
- MySQL or SQLite

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/felix7word/laravel-light-api.git
   cd laravel-light-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Edit `.env` file to set your database credentials.

4. **Run migrations**
   ```bash
   php artisan migrate
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   ```

## API Documentation

API documentation is automatically generated using [Scribe](https://scribe.knuckles.wtf/laravel). You can access the documentation at:

```
http://localhost:8000/docs
```

<p align="center">
  <img src="https://trae-api-cn.mchost.guru/api/ide/v1/text_to_image?prompt=API%20documentation%20interface%20for%20Laravel%20API%20showing%20endpoints%2C%20request%20parameters%2C%20and%20response%20examples%2C%20professional%20clean%20interface%20with%20code%20snippets%2C%20blue%20and%20white%20color%20scheme&image_size=landscape_16_9" alt="API Documentation" width="800">
</p>

To regenerate the documentation after making changes to your API:

```bash
php artisan scribe:generate
```

## API Endpoints

### Authentication

- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login a user
- `POST /api/auth/logout` - Logout a user (requires authentication)
- `GET /api/auth/user` - Get the current user (requires authentication)

### Posts

- `GET /api/posts` - List all posts with filtering and sorting
- `POST /api/posts` - Create a new post (requires authentication)
- `GET /api/posts/{id}` - Get a single post
- `PUT/PATCH /api/posts/{id}` - Update a post (requires authentication)
- `DELETE /api/posts/{id}` - Delete a post (requires authentication)

## Example Usage

### Register a user

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com", "password": "password123"}'
```

### Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com", "password": "password123"}'
```

### Create a post

```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"title": "Laravel API", "content": "Building APIs with Laravel"}'
```

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/           # API controllers
│   │   └── Controller.php  # Base controller
│   └── Middleware/
├── Models/                 # Eloquent models
├── Policies/               # Authorization policies
└── Traits/                 # Reusable traits
    └── ApiResponse.php     # API response formatting

routes/
└── api.php                # API routes

database/
└── migrations/            # Database migrations

.storage/app/scribe/       # API documentation files
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Steps to contribute

1. Fork the repository
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run `composer run format` to fix code style
5. Run `php artisan test` to ensure all tests pass
6. Commit your changes (`git commit -m 'Add amazing feature'`)
7. Push to the branch (`git push origin feature/amazing-feature`)
8. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Deployment

### Shared Hosting

1. **Upload files**
   - Upload the project files to your shared hosting account
   - Make sure to place the contents of the `public` directory in your web root

2. **Configure environment**
   - Create a `.env` file based on `.env.example`
   - Set your database credentials and other configuration

3. **Run migrations**
   - Access your hosting's SSH terminal
   - Navigate to the project directory
   - Run `php artisan migrate`

4. **Optimize**
   - Run `php artisan config:cache`
   - Run `php artisan route:cache`

### VPS (Ubuntu)

1. **Install dependencies**
   ```bash
   sudo apt update
   sudo apt install php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl
   sudo apt install nginx mysql-server
   sudo apt install composer
   ```

2. **Configure Nginx**
   ```nginx
   # /etc/nginx/sites-available/laravel-api
   server {
       listen 80;
       server_name api.example.com;
       root /var/www/laravel-light-api/public;
       
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/run/php/php8.1-fpm.sock;
       }
   }
   ```

3. **Deploy project**
   ```bash
   cd /var/www
   git clone https://github.com/felix7word/laravel-light-api.git
   cd laravel-light-api
   composer install
   cp .env.example .env
   php artisan key:generate
   # Edit .env file
   php artisan migrate
   php artisan config:cache
   php artisan route:cache
   sudo chown -R www-data:www-data storage/
   ```

### Docker

1. **Create Dockerfile**
   ```dockerfile
   # Dockerfile
   FROM php:8.1-fpm
   
   WORKDIR /var/www/html
   
   RUN apt-get update && apt-get install -y 
       libzip-dev 
       unzip 
       git 
       && docker-php-ext-install zip pdo_mysql
   
   RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
   
   COPY . .
   
   RUN composer install --no-dev --optimize-autoloader
   
   RUN php artisan key:generate
   
   EXPOSE 9000
   
   CMD ["php-fpm"]
   ```

2. **Create docker-compose.yml**
   ```yaml
   version: '3'
   services:
     app:
       build: .
       volumes:
         - .:/var/www/html
       depends_on:
         - db
     db:
       image: mysql:8.0
       environment:
         MYSQL_ROOT_PASSWORD: root
         MYSQL_DATABASE: laravel_api
         MYSQL_USER: laravel
         MYSQL_PASSWORD: password
       ports:
         - "3306:3306"
     nginx:
       image: nginx:alpine
       volumes:
         - .:/var/www/html
         - ./nginx.conf:/etc/nginx/conf.d/default.conf
       ports:
         - "80:80"
       depends_on:
         - app
   ```

3. **Create nginx.conf**
   ```nginx
   server {
       listen 80;
       server_name localhost;
       root /var/www/html/public;
       
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass app:9000;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

4. **Run containers**
   ```bash
   docker-compose up -d
   docker-compose exec app php artisan migrate
   ```

### Heroku

1. **Create Heroku app**
   ```bash
   heroku create your-api-app
   ```

2. **Set up database**
   ```bash
   heroku addons:create heroku-postgresql:mini
   ```

3. **Configure environment**
   ```bash
   heroku config:set APP_KEY=$(php artisan key:generate --show)
   heroku config:set APP_ENV=production
   heroku config:set APP_DEBUG=false
   ```

4. **Deploy**
   ```bash
   git push heroku main
   heroku run php artisan migrate
   ```

## Support

If you like this project, please consider giving it a ⭐ star on GitHub!
