#!/usr/bin/env bash
set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "Updating from Git..."
cd "$PROJECT_DIR"
git pull

echo "Stopping and removing existing containers..."
docker-compose rm -f --stop || true
docker-compose down --remove-orphans || true

echo "Rebuilding and restarting containers..."
docker-compose pull || true
docker-compose build
docker-compose up -d

echo "Update finished. All services have been restarted with the latest code."

