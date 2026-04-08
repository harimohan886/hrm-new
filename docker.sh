#!/bin/bash
# Docker Compose commands for HRM Application

# Start all services
start() {
    echo "Starting HRM Application..."
    docker-compose up -d
    echo "Application started at http://localhost:8000"
    echo "phpMyAdmin available at http://localhost:8080"
    echo "MailHog available at http://localhost:8025"
}

# Stop all services
stop() {
    echo "Stopping HRM Application..."
    docker-compose down
}

# Restart all services
restart() {
    echo "Restarting HRM Application..."
    docker-compose restart
}

# View logs
logs() {
    docker-compose logs -f
}

# Run Laravel commands
artisan() {
    docker-compose exec app php artisan "$@"
}

# Run composer commands
composer() {
    docker-compose exec app composer "$@"
}

# Access application container
shell() {
    docker-compose exec app sh
}

# Build and start fresh
build() {
    echo "Building and starting HRM Application..."
    docker-compose down
    docker-compose build --no-cache
    docker-compose up -d
    echo "Application built and started at http://localhost:8000"
}

# Show help
help() {
    echo "HRM Docker Commands:"
    echo "  start     - Start all services"
    echo "  stop      - Stop all services"
    echo "  restart   - Restart all services"
    echo "  build     - Build and start fresh"
    echo "  logs      - View application logs"
    echo "  artisan   - Run Laravel artisan commands"
    echo "  composer  - Run composer commands"
    echo "  shell     - Access application container"
    echo "  help      - Show this help message"
}

# Check if function exists
if declare -f "$1" > /dev/null; then
    "$@"
else
    echo "Error: Command '$1' not found"
    help
fi