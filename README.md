# Bot Handler - Multi-Service Dashboard

داشبورد مدیریت چند سرویس و ربات تلگرام با معماری میکروسرویس

## 🚀 نصب اولیه روی سرور

### نصب یک‌خطی (توصیه می‌شود)

```bash
curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/bothandler/main/scripts/install.sh | sudo bash
```

این دستور به طور خودکار:
- Repository را clone می‌کند
- Docker و Docker Compose را نصب می‌کند
- یک کلید امنیتی (INTERNAL_API_KEY) برای ارتباط بین سرویس‌ها تولید می‌کند
- تمام سرویس‌ها را راه‌اندازی می‌کند
- Migrations را اجرا می‌کند

### تنظیمات اختیاری

```bash
# تغییر مسیر نصب (پیش‌فرض: /opt/bothandler)
curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/bothandler/main/scripts/install.sh | INSTALL_DIR=/var/www/bothandler sudo bash

# نصب از branch دیگر (پیش‌فرض: main)
curl -fsSL https://raw.githubusercontent.com/arsalanarghavan/bothandler/main/scripts/install.sh | BRANCH=develop sudo bash
```

## 🔐 امنیت

### INTERNAL_API_KEY

برای امنیت ارتباط بین سرویس‌ها (api-gateway، monitoring-service، bot-manager)، یک کلید مشترک استفاده می‌شود.

**تنظیم خودکار:**
- در زمان نصب: اسکریپت نصب به صورت خودکار یک کلید 64 کاراکتری تولید و در همه سرویس‌ها تنظیم می‌کند
- در زمان Setup Wizard: یک کلید جدید تولید و در همه سرویس‌ها جایگزین می‌شود

**تنظیم دستی (در صورت نیاز):**
```bash
# تولید یک کلید جدید
NEW_KEY=$(openssl rand -hex 32)

# تنظیم در همه سرویس‌ها
for service in api-gateway monitoring-service bot-manager; do
  echo "INTERNAL_API_KEY=$NEW_KEY" >> /opt/bothandler/backend/$service/.env
done

# Restart سرویس‌ها
cd /opt/bothandler
docker-compose restart api-gateway monitoring-service bot-manager
```

**⚠️ مهم:** این کلید باید در همه 3 سرویس یکسان باشد.

## 📦 آپدیت پروژه

### روش یک خطی

```bash
cd /opt/bothandler && git pull && chmod +x scripts/update.sh && ./scripts/update.sh
```

### روش دستی

```bash
cd /opt/bothandler
git pull
chmod +x scripts/update.sh
./scripts/update.sh
```

## 🔧 دستورات توسعه و نگهداری

### Migration Database

```bash
# داخل کانتینر api-gateway
docker exec -it bothandler_api-gateway php artisan migrate

# یا از خارج
cd /opt/bothandler/backend/api-gateway
docker-compose exec api-gateway php artisan migrate
```

### مشاهده لاگ‌ها

```bash
# همه سرویس‌ها
cd /opt/bothandler
docker-compose logs -f

# سرویس خاص
docker-compose logs -f api-gateway
docker-compose logs -f frontend
docker-compose logs -f bot-manager
docker-compose logs -f monitoring-service
```

### Restart سرویس‌ها

```bash
cd /opt/bothandler

# Restart همه
docker-compose restart

# Restart سرویس خاص
docker-compose restart api-gateway
docker-compose restart frontend
```

### Stop/Start سرویس‌ها

```bash
cd /opt/bothandler

# Stop همه
docker-compose stop

# Start همه
docker-compose start

# Stop سرویس خاص
docker-compose stop api-gateway
```

### Rebuild سرویس‌ها

```bash
cd /opt/bothandler

# Rebuild همه
docker-compose build --no-cache

# Rebuild سرویس خاص
docker-compose build --no-cache api-gateway

# سپس restart
docker-compose up -d
```

### مشاهده وضعیت سرویس‌ها

```bash
cd /opt/bothandler
docker-compose ps
```

### دسترسی به Shell داخل کانتینر

```bash
# API Gateway
docker exec -it bothandler_api-gateway bash

# Frontend
docker exec -it bothandler_frontend sh

# Bot Manager
docker exec -it bothandler_bot-manager bash

# Monitoring Service
docker exec -it bothandler_monitoring-service bash
```

### اجرای دستورات Artisan

```bash
# داخل کانتینر
docker exec -it bothandler_api-gateway php artisan [command]

# مثال: Clear cache
docker exec -it bothandler_api-gateway php artisan cache:clear
docker exec -it bothandler_api-gateway php artisan config:clear
docker exec -it bothandler_api-gateway php artisan route:clear
```

### مشاهده مصرف منابع

```bash
# همه کانتینرها
docker stats

# کانتینر خاص
docker stats bothandler_api-gateway
```

### Backup Database

```bash
# اگر از MySQL استفاده می‌کنید
docker exec bothandler_mysql mysqldump -u root -p[password] [database] > backup.sql

# SQLite (اگر استفاده می‌کنید)
docker cp bothandler_api-gateway:/var/www/html/database/database.sqlite ./backup.sqlite
```

### پاک کردن همه چیز و شروع مجدد

```bash
cd /opt/bothandler

# Stop و remove همه کانتینرها
docker-compose down -v

# Remove همه images
docker-compose rm -f

# پاک کردن volumes
docker volume prune -f

# سپس دوباره نصب
./scripts/install.sh
```

## 🌐 دسترسی به داشبورد

### بعد از نصب اولیه

1. **با IP و Port (موقت):**
   ```
   http://YOUR_SERVER_IP:8080
   ```

2. **با دامنه (بعد از تنظیم DNS):**
   ```
   http://YOUR_DOMAIN
   ```

### Setup Wizard

بعد از اولین دسترسی، Setup Wizard نمایش داده می‌شود که باید:
- نام داشبورد
- دامنه داشبورد
- ایمیل ادمین
- نام کاربری ادمین
- رمز عبور

را وارد کنید.

### Login

بعد از تکمیل Setup، به صفحه Login هدایت می‌شوید:
- ایمیل: همان ایمیل وارد شده در Setup
- رمز عبور: همان رمز عبور وارد شده در Setup

## 🔐 تنظیمات SSL

بعد از تنظیم DNS و تکمیل Setup Wizard، SSL به صورت خودکار با Let's Encrypt تنظیم می‌شود.

برای تنظیم دستی:

```bash
# داخل کانتینر nginx-proxy-acme
docker exec -it nginx-proxy-acme certbot certonly --standalone -d YOUR_DOMAIN
```

## 📝 ساختار پروژه

```
bothandler/
├── backend/
│   ├── api-gateway/          # API Gateway (Laravel)
│   ├── bot-manager/          # Bot Manager Service (Laravel)
│   └── monitoring-service/   # Monitoring Service (Laravel)
├── frontend/                 # Vue.js Frontend
├── scripts/
│   ├── install.sh            # اسکریپت نصب
│   └── update.sh             # اسکریپت آپدیت
├── docker-compose.yml        # Docker Compose Configuration
└── README.md                 # این فایل
```

## 🛠️ توسعه

### نصب Dependencies

```bash
# Backend (Laravel)
cd backend/api-gateway
composer install

# Frontend (Vue.js)
cd frontend
npm install
```

### Build Frontend

```bash
cd frontend
npm run build
```

### Development Mode

```bash
# Frontend (Hot Reload)
cd frontend
npm run dev

# Backend (Laravel Serve)
cd backend/api-gateway
php artisan serve
```

## 🐛 عیب‌یابی

### مشکل: کانتینرها بالا نمی‌آیند

```bash
# چک کردن لاگ‌ها
docker-compose logs

# چک کردن وضعیت
docker-compose ps

# Rebuild و restart
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### مشکل: خطای Database

```bash
# اجرای migration
docker exec -it bothandler_api-gateway php artisan migrate:fresh

# یا reset کامل
docker exec -it bothandler_api-gateway php artisan migrate:reset
docker exec -it bothandler_api-gateway php artisan migrate
```

### مشکل: خطای Permission

```bash
# تنظیم permission برای volumes
sudo chown -R $USER:$USER /opt/bothandler
sudo chmod -R 755 /opt/bothandler
```

### مشکل: Port در حال استفاده

```bash
# پیدا کردن process که از port استفاده می‌کند
sudo lsof -i :80
sudo lsof -i :443
sudo lsof -i :8080

# Kill کردن process
sudo kill -9 [PID]
```

## 📞 پشتیبانی

برای مشکلات و سوالات:
- GitHub Issues: https://github.com/arsalanarghavan/bothandler/issues
- Email: [your-email@example.com]

## 📄 لایسنس

MIT License
