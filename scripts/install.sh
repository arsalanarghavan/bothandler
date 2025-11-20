#!/usr/bin/env bash
set -e

# Installation directory
INSTALL_DIR="${INSTALL_DIR:-/opt/bothandler}"
REPO_URL="https://github.com/arsalanarghavan/bothandler.git"
BRANCH="${BRANCH:-main}"

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Progress bar function
show_progress() {
    local current=$1
    local total=$2
    local message=$3
    local percent=$((current * 100 / total))
    local filled=$((percent / 2))
    local empty=$((50 - filled))
    
    printf "\r${BLUE}[%-50s]${NC} ${GREEN}%d%%${NC} - %s" \
           "$(printf '%*s' "$filled" | tr ' ' '█')$(printf '%*s' "$empty")" \
           "$percent" \
           "$message"
}

# Clear screen and show header (skip if running via curl | bash)
[ -t 0 ] && clear || true
cat << "EOF"

╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║            Bot Hosting Dashboard Installer                  ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝

EOF

echo ""

# Step 1: Clone/Update Repository (10%)
show_progress 1 10 "Preparing installation directory..."
if [ -d "$INSTALL_DIR/.git" ]; then
  cd "$INSTALL_DIR"
  git fetch origin &>/dev/null
  git reset --hard origin/$BRANCH &>/dev/null
else
  if [ -d "$INSTALL_DIR" ]; then
    echo ""
    echo -e "${RED}Error: Directory $INSTALL_DIR exists but is not a git repository.${NC}"
    echo "Please remove it or choose a different installation directory."
    exit 1
  fi
  git clone -b $BRANCH "$REPO_URL" "$INSTALL_DIR" &>/dev/null
  cd "$INSTALL_DIR"
fi
PROJECT_DIR="$INSTALL_DIR"
export PROJECT_ROOT="$INSTALL_DIR"

# Step 2: Update System (20%)
show_progress 2 10 "Updating system packages..."
sudo apt-get update -y &>/dev/null
sudo apt-get upgrade -y &>/dev/null

# Step 3: Install Docker (30%)
show_progress 3 10 "Installing Docker & Docker Compose..."
if ! command -v docker >/dev/null 2>&1; then
  curl -fsSL https://get.docker.com | sh &>/dev/null
fi
sudo usermod -aG docker "$USER" &>/dev/null || true

if ! command -v docker-compose >/dev/null 2>&1; then
  # Try to install docker compose plugin first (newer method)
  if ! docker compose version &>/dev/null; then
    # Fallback to standalone docker-compose
    DOCKER_COMPOSE_VERSION="2.24.0"
    sudo curl -L "https://github.com/docker/compose/releases/download/v${DOCKER_COMPOSE_VERSION}/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose &>/dev/null || \
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose &>/dev/null
    sudo chmod +x /usr/local/bin/docker-compose
  fi
fi

# Step 4: Prepare Environment Files (40%)
show_progress 4 10 "Preparing service configurations..."
cd "$PROJECT_DIR"

# Create root .env if it doesn't exist
if [ ! -f ".env" ] && [ -f ".env.example" ]; then
  cp ".env.example" ".env"
fi

for service in api-gateway monitoring-service bot-manager; do
  SERVICE_DIR="backend/$service"
  if [ -d "$SERVICE_DIR" ]; then
    if [ ! -f "$SERVICE_DIR/.env" ]; then
      if [ -f "$SERVICE_DIR/.env.example" ]; then
        cp "$SERVICE_DIR/.env.example" "$SERVICE_DIR/.env"
        if command -v php >/dev/null 2>&1; then
          (cd "$SERVICE_DIR" && php artisan key:generate --ansi &>/dev/null || true)
        fi
      fi
    fi
    if [ ! -f "$SERVICE_DIR/database/database.sqlite" ]; then
      touch "$SERVICE_DIR/database/database.sqlite"
      chmod 666 "$SERVICE_DIR/database/database.sqlite" &>/dev/null || true
    fi
  fi
done

if [ ! -f "frontend/.env" ] && [ -f "frontend/.env.example" ]; then
  cp "frontend/.env.example" "frontend/.env"
fi

# Step 5: Generate Security Keys (50%)
show_progress 5 10 "Generating security keys..."
INTERNAL_API_KEY=$(openssl rand -hex 32 2>/dev/null || head -c 32 /dev/urandom | xxd -p -c 64)

for service in api-gateway monitoring-service bot-manager; do
  SERVICE_ENV="backend/$service/.env"
  if [ -f "$SERVICE_ENV" ]; then
    if grep -q "^INTERNAL_API_KEY=" "$SERVICE_ENV"; then
      sed -i "s|^INTERNAL_API_KEY=.*|INTERNAL_API_KEY=$INTERNAL_API_KEY|" "$SERVICE_ENV"
    else
      echo "INTERNAL_API_KEY=$INTERNAL_API_KEY" >> "$SERVICE_ENV"
    fi
  fi
done

# Step 6: Clean Up Old Containers (60%)
show_progress 6 10 "Cleaning up old installations..."
cd "$PROJECT_DIR"
docker ps -a --format "{{.ID}} {{.Names}}" | grep -i bothandler | awk '{print $1}' | xargs -r docker rm -f &>/dev/null || true
docker-compose -f "$PROJECT_DIR/docker-compose.yml" down --remove-orphans -v &>/dev/null || true
docker image prune -f &>/dev/null || true

# Step 7: Pull Docker Images (70%)
show_progress 7 10 "Downloading required images..."
docker-compose -f "$PROJECT_DIR/docker-compose.yml" pull &>/dev/null || true

# Step 8: Build Services (80%)
show_progress 8 10 "Building application services..."
docker-compose -f "$PROJECT_DIR/docker-compose.yml" build &>/dev/null

# Step 9: Start Services (90%)
show_progress 9 10 "Starting all services..."
docker-compose -f "$PROJECT_DIR/docker-compose.yml" up -d &>/dev/null
sleep 10

# Step 10: Initialize Database (100%)
show_progress 10 10 "Initializing database..."
# Wait for services to be ready
sleep 10
# Run migrations (retry if needed)
for i in 1 2 3; do
    docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T api-gateway php artisan migrate --force >/dev/null 2>&1 && break || sleep 5
done
for i in 1 2 3; do
    docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T monitoring-service php artisan migrate --force >/dev/null 2>&1 && break || sleep 5
done
for i in 1 2 3; do
    docker-compose -f "$PROJECT_DIR/docker-compose.yml" exec -T bot-manager php artisan migrate --force >/dev/null 2>&1 && break || sleep 5
done

# Get server IP (try multiple methods)
SERVER_IP="$(hostname -I 2>/dev/null | awk '{print $1}')"
if [ -z "$SERVER_IP" ]; then
  SERVER_IP="$(ip route get 8.8.8.8 2>/dev/null | awk '{print $7; exit}')"
fi
if [ -z "$SERVER_IP" ]; then
  SERVER_IP="$(curl -s ifconfig.me 2>/dev/null || curl -s icanhazip.com 2>/dev/null || echo 'YOUR_SERVER_IP')"
fi

# Clear progress line and show success
echo ""
echo ""
echo -e "${GREEN}✓ Installation completed successfully!${NC}"
echo ""

# Display success message
cat << EOF
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║          ✅  INSTALLATION COMPLETED SUCCESSFULLY! ✅         ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚀  NEXT STEP: Complete the Setup Wizard
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  Open this URL in your browser:

  👉  http://$SERVER_IP:8080/setup

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📝  What you'll need to provide:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  1. Dashboard Name    (e.g., "My Bot Manager")
  2. Domain Name       (e.g., "bothandler.example.com")
  3. Admin Name        (Your full name)
  4. Admin Email       (Your email address)
  5. Admin Password    (Strong password)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⚡ What happens automatically:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  ✓ SSL Certificate (Let's Encrypt)
  ✓ Domain Configuration
  ✓ Security Keys Generation
  ✓ Service Restart

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
💡 Tips:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  • Make sure your domain DNS points to: $SERVER_IP
  • The setup wizard takes about 30 seconds to complete
  • After setup, login at: http://YOUR_DOMAIN/login

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🎉  Enjoy your Bot Hosting Dashboard!

EOF
