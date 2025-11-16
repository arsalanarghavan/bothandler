# دستورات اعمال تغییرات

## 🔄 اگر روی سرور هستی (توصیه می‌شود)

```bash
# 1. Pull تغییرات از گیت
cd /opt/bothandler
git pull

# 2. اجرای migration (اگر migration جدید داریم)
docker exec -it bothandler_api-gateway php artisan migrate

# 3. Rebuild و restart سرویس‌ها
chmod +x scripts/update.sh
./scripts/update.sh
```

## 💻 اگر روی local هستی و می‌خوای push کنی

```bash
# 1. اضافه کردن همه تغییرات
cd /mnt/1AF200F7F200D941/Projects/Bots/bothandler
git add .

# 2. Commit
git commit -m "Complete Xintra theme integration, authentication, and setup wizard"

# 3. Push
git push origin main

# 4. سپس روی سرور pull کن
```

## 🚀 اگر می‌خوای از صفر نصب کنی

```bash
# یک خطی
bash -c "cd /opt && rm -rf bothandler && git clone https://github.com/arsalanarghavan/bothandler.git /opt/bothandler && cd /opt/bothandler && chmod +x scripts/install.sh && ./scripts/install.sh"
```

## ⚡ دستور سریع (اگر الان روی سرور هستی)

```bash
cd /opt/bothandler && git pull && docker exec -it bothandler_api-gateway php artisan migrate && chmod +x scripts/update.sh && ./scripts/update.sh
```

