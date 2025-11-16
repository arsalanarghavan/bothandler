#!/usr/bin/env bash
set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "Updating from Git..."
cd "$PROJECT_DIR"
git pull

echo "Stopping and removing existing containers..."
# Force remove ALL containers with "bothandler" in their name (including orphaned ones)
docker ps -a --format "{{.ID}} {{.Names}}" | grep -i bothandler | awk '{print $1}' | xargs -r docker rm -f || true
# Remove all containers with hash-based names that might be orphaned
docker ps -a --format "{{.ID}} {{.Names}}" | grep -E "bothandler|_[0-9a-f]+_" | awk '{print $1}' | xargs -r docker rm -f || true
# Also try docker-compose cleanup
docker-compose rm -f --stop || true
docker-compose down --remove-orphans -v || true
# Remove dangling images
docker image prune -f || true

echo "Rebuilding and restarting containers..."
docker-compose pull || true
docker-compose build
docker-compose up -d

echo "Update finished. All services have been restarted with the latest code."

