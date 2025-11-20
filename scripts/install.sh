#!/usr/bin/env bash
set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "Updating system packages..."
sudo apt-get update -y
sudo apt-get upgrade -y

echo "Installing Docker and Docker Compose..."
if ! command -v docker >/dev/null 2>&1; then
  curl -fsSL https://get.docker.com | sh
fi

sudo usermod -aG docker "$USER" || true

if ! command -v docker-compose >/dev/null 2>&1; then
  sudo curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
  sudo chmod +x /usr/local/bin/docker-compose
fi

echo "Preparing Laravel service environments (.env & APP_KEY)..."
cd "$PROJECT_DIR"
for service in api-gateway monitoring-service bot-manager; do
  SERVICE_DIR="backend/$service"
  if [ -d "$SERVICE_DIR" ]; then
    if [ ! -f "$SERVICE_DIR/.env" ]; then
      if [ -f "$SERVICE_DIR/.env.example" ]; then
        cp "$SERVICE_DIR/.env.example" "$SERVICE_DIR/.env"
        if command -v php >/dev/null 2>&1; then
          (cd "$SERVICE_DIR" && php artisan key:generate --ansi || true)
        fi
      fi
    fi
    # Create SQLite database if it doesn't exist
    if [ ! -f "$SERVICE_DIR/database/database.sqlite" ]; then
      touch "$SERVICE_DIR/database/database.sqlite"
      chmod 666 "$SERVICE_DIR/database/database.sqlite" || true
    fi
  fi
done

echo "Stopping and removing any existing containers..."
cd "$PROJECT_DIR"
# Force remove ALL containers with "bothandler" in their name (including orphaned ones)
docker ps -a --format "{{.ID}} {{.Names}}" | grep -i bothandler | awk '{print $1}' | xargs -r docker rm -f || true
# Remove all containers with hash-based names that might be orphaned
docker ps -a --format "{{.ID}} {{.Names}}" | grep -E "bothandler|_[0-9a-f]+_" | awk '{print $1}' | xargs -r docker rm -f || true
# Also try docker-compose cleanup
docker-compose -f "$PROJECT_DIR/docker-compose.yml" rm -f --stop || true
docker-compose -f "$PROJECT_DIR/docker-compose.yml" down --remove-orphans -v || true
# Remove dangling images
docker image prune -f || true

echo "Bringing up containers..."
cd "$PROJECT_DIR"
docker-compose -f "$PROJECT_DIR/docker-compose.yml" pull || true
docker-compose -f "$PROJECT_DIR/docker-compose.yml" build
docker-compose -f "$PROJECT_DIR/docker-compose.yml" up -d

echo "Waiting for services to be ready..."
sleep 10

echo "Running database migrations..."
docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T api-gateway php artisan migrate --force || echo "Warning: API Gateway migrations failed"
docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T monitoring-service php artisan migrate --force || echo "Warning: Monitoring Service migrations failed"
docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T bot-manager php artisan migrate --force || echo "Warning: Bot Manager migrations failed"

SERVER_IP="$(hostname -I 2>/dev/null | awk '{print $1}')"

echo ""
echo "========================================="
echo "Installation finished successfully!"
echo "========================================="
echo ""
echo "Access your dashboard at:"
echo "  - Temporary URL (IP): http://$SERVER_IP:8080/"
echo "  - Production URL: http://YOUR_DOMAIN (after DNS setup)"
echo ""
echo "Complete the setup wizard to:"
echo "  1. Set dashboard name and domain"
echo "  2. Create admin account"
echo "  3. Configure SSL automatically"
echo ""


