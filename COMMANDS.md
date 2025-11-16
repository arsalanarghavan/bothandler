# دستورات سریع - Quick Commands

## 🚀 نصب اولیه

```bash
# یک خطی (توصیه می‌شود)
bash -c "cd /opt && rm -rf bothandler && git clone https://github.com/arsalanarghavan/bothandler.git /opt/bothandler && cd /opt/bothandler && chmod +x scripts/install.sh && ./scripts/install.sh"
```

## 🔄 آپدیت

```bash
cd /opt/bothandler && git pull && chmod +x scripts/update.sh && ./scripts/update.sh
```

## 📊 مشاهده وضعیت

```bash
# وضعیت کانتینرها
cd /opt/bothandler && docker-compose ps

# لاگ‌های همه سرویس‌ها
cd /opt/bothandler && docker-compose logs -f

# مصرف منابع
docker stats
```

## 🔧 Migration

```bash
# اجرای migration
docker exec -it bothandler_api-gateway php artisan migrate

# Reset و migrate مجدد
docker exec -it bothandler_api-gateway php artisan migrate:fresh
```

## 🔄 Restart

```bash
# Restart همه
cd /opt/bothandler && docker-compose restart

# Restart سرویس خاص
cd /opt/bothandler && docker-compose restart api-gateway
```

## 🛑 Stop/Start

```bash
# Stop همه
cd /opt/bothandler && docker-compose stop

# Start همه
cd /opt/bothandler && docker-compose start
```

## 🗑️ پاک کردن و شروع مجدد

```bash
cd /opt/bothandler
docker-compose down -v
docker-compose rm -f
docker volume prune -f
./scripts/install.sh
```

## 🐚 دسترسی به Shell

```bash
# API Gateway
docker exec -it bothandler_api-gateway bash

# Frontend
docker exec -it bothandler_frontend sh
```

## 🧹 پاک کردن Cache

```bash
docker exec -it bothandler_api-gateway php artisan cache:clear
docker exec -it bothandler_api-gateway php artisan config:clear
docker exec -it bothandler_api-gateway php artisan route:clear
```

## 📝 دستورات Artisan

```bash
# لیست همه دستورات
docker exec -it bothandler_api-gateway php artisan list

# Generate Key
docker exec -it bothandler_api-gateway php artisan key:generate

# Make Migration
docker exec -it bothandler_api-gateway php artisan make:migration create_example_table

# Make Controller
docker exec -it bothandler_api-gateway php artisan make:controller ExampleController
```

## 🌐 دسترسی

- **موقت (IP):** `http://YOUR_SERVER_IP:8080`
- **دامنه:** `http://YOUR_DOMAIN` (بعد از تنظیم DNS)

## 🔐 تنظیمات اولیه

بعد از اولین دسترسی:
1. Setup Wizard نمایش داده می‌شود
2. اطلاعات را وارد کنید
3. به صفحه Login هدایت می‌شوید
4. با همان اطلاعات وارد شوید

