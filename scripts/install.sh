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
  fi
done

echo "Stopping and removing any existing containers..."
cd "$PROJECT_DIR"
# Force remove all containers with "bothandler" in their name (including orphaned ones)
docker ps -a --filter "name=bothandler" --format "{{.ID}}" | xargs -r docker rm -f || true
# Also try docker-compose cleanup
docker-compose rm -f --stop || true
docker-compose down --remove-orphans || true

echo "Bringing up containers..."
cd "$PROJECT_DIR"
docker-compose pull || true
docker-compose build
docker-compose up -d

SERVER_IP="$(hostname -I 2>/dev/null | awk '{print $1}')"

echo "Installation finished."
echo "Admin panel (temporary, with server IP):  http://$SERVER_IP:8080/"
echo "For production, point your dashboard domain to this server IP and open it in the browser (port 80);"
echo "the setup wizard will ask for the domain and configure everything (including SSL) automatically."


