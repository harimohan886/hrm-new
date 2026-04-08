# HRM Application Docker Setup

This Laravel-based Human Resource Management (HRM) application can be easily deployed using Docker.

## Quick Start

### Prerequisites
- Docker and Docker Compose installed
- At least 4GB of available RAM
- Ports 8000, 8080, 8025, 3309, 6379 available

### 1. Clone and Setup
```bash
git clone <repository-url>
cd hrm2
cp .env.docker .env
```

### 2. Build and Run
```bash
# Using the provided script (recommended)
./docker.sh build

# Or manually
docker-compose build --no-cache
docker-compose up -d
```

### 3. Initialize Application
```bash
# Run migrations and seed database
./docker.sh artisan migrate --seed

# Generate application key if needed
./docker.sh artisan key:generate

# Create storage link
./docker.sh artisan storage:link

# Clear and cache config
./docker.sh artisan config:cache
./docker.sh artisan route:cache
```

## Services

| Service | URL | Purpose |
|---------|-----|---------|
| **Application** | http://localhost:8000 | Main HRM application |
| **phpMyAdmin** | http://localhost:8080 | Database management |
| **MailHog** | http://localhost:8025 | Email testing interface |

## Docker Commands

Use the provided `docker.sh` script for easy management:

```bash
./docker.sh start      # Start all services
./docker.sh stop       # Stop all services
./docker.sh restart    # Restart services
./docker.sh logs       # View logs
./docker.sh shell      # Access app container
./docker.sh artisan    # Run Laravel commands
./docker.sh composer   # Run Composer commands
./docker.sh build      # Full rebuild
```

### Direct Docker Compose Commands
```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f app

# Execute commands in app container
docker-compose exec app php artisan migrate
docker-compose exec app composer install
```

## Architecture

### Services Overview:
- **app**: Laravel application (PHP 8.1, Nginx)
- **mysql**: MySQL 8.0 database
- **redis**: Redis for caching and sessions
- **mailhog**: Email testing service
- **node**: Node.js for asset compilation
- **phpmyadmin**: Database administration

### Volumes:
- `mysql_data`: Persistent MySQL database
- `redis_data`: Redis data persistence
- Application files mounted from host

### Network:
- All services connected via `hrm_network` bridge

## Configuration

### Environment Variables
- Copy `.env.docker` to `.env` for Docker environment
- Database: `mysql:3306` (accessible on host `localhost:3309`)
- Redis: `redis:6379` 
- Mail: `mailhog:1025`

### Database Import
The existing database backup (`hrm_backup_20260406_132748.sql`) is automatically imported on first MySQL container start.

## Development

### Asset Compilation
```bash
# Install dependencies
./docker.sh shell
npm install

# Development build
npm run dev

# Watch for changes
npm run watch

# Production build
npm run production
```

### Laravel Commands
```bash
# Create migration
./docker.sh artisan make:migration create_users_table

# Create controller
./docker.sh artisan make:controller UserController

# Run tests
./docker.sh artisan test

# Create module (if using nwidart/laravel-modules)
./docker.sh artisan module:make ModuleName
```

## Troubleshooting

### Common Issues:

1. **Port conflicts**: Change ports in `docker-compose.yml`
2. **Permission issues**: Run `chmod -R 755 storage bootstrap/cache`
3. **Database connection**: Ensure MySQL service is running
4. **Asset compilation**: Run `npm install && npm run dev`

### Logs:
```bash
# Application logs
./docker.sh logs

# Specific service logs
docker-compose logs mysql
docker-compose logs redis
```

### Reset Everything:
```bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

## Production Deployment

For production:
1. Update `.env` with production settings
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Use proper database credentials
4. Configure SSL/HTTPS
5. Set up proper backup strategy
6. Use external Redis/database services if needed

## License

Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).