#!/usr/bin/env bash
set -e

echo "Cleaning up all bothandler containers and images..."

# Stop all containers
docker ps -a --format "{{.ID}} {{.Names}}" | grep -i bothandler | awk '{print $1}' | xargs -r docker stop || true

# Remove all containers
docker ps -a --format "{{.ID}} {{.Names}}" | grep -i bothandler | awk '{print $1}' | xargs -r docker rm -f || true

# Remove containers with hash-based names
docker ps -a --format "{{.ID}} {{.Names}}" | grep -E "bothandler|_[0-9a-f]+_" | awk '{print $1}' | xargs -r docker rm -f || true

# Docker compose cleanup
cd "$(dirname "${BASH_SOURCE[0]}")/.."
docker-compose down -v --remove-orphans || true
docker-compose rm -f || true

# Remove dangling images
docker image prune -f || true

# Remove bothandler images
docker images | grep bothandler | awk '{print $3}' | xargs -r docker rmi -f || true

echo "Cleanup complete!"

