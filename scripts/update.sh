#!/usr/bin/env bash
set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "========================================="
echo "Bot Hosting Dashboard Updater"
echo "========================================="
echo ""

cd "$PROJECT_DIR"

echo "Pulling latest changes from repository..."
git fetch origin
git pull origin main

echo "Stopping containers..."
docker-compose -f "$PROJECT_DIR/docker-compose.yml" down

echo "Building updated images..."
docker-compose -f "$PROJECT_DIR/docker-compose.yml" build --no-cache

echo "Starting containers..."
docker-compose -f "$PROJECT_DIR/docker-compose.yml" up -d

echo "Waiting for services to be ready..."
sleep 10

echo "Running database migrations..."
docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T api-gateway php artisan migrate --force || echo "Warning: API Gateway migrations failed"
docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T monitoring-service php artisan migrate --force || echo "Warning: Monitoring Service migrations failed"
docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T bot-manager php artisan migrate --force || echo "Warning: Bot Manager migrations failed"

echo ""
echo "========================================="
echo "Update completed successfully!"
echo "========================================="
echo ""
