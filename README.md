# 🏥 نظام إدارة العيادات الموحّد - Backend

<p align="center">
  <a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.7.0-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/Database-PostgreSQL-blue.svg" alt="Database">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License">
</p>

## 📋 نظرة عامة

هذا هو Backend لنظام إدارة العيادات الموحّد، وهو منصة متكاملة تربط المرضى بالعيادات والأطباء عبر نظام واحد مع ملف طبي مركزي للمريض.

## 🚀 التقنيات المستخدمة

- **Framework**: Laravel 13.7.0
- **Auth**: Laravel Sanctum (Multi-role authentication)
- **Database**: PostgreSQL
- **Queue**: Laravel Horizon + Redis
- **Storage**: S3 / Cloudflare R2
- **Search**: Laravel Scout + Meilisearch
- **API Docs**: Scribe / Swagger
- **Notifications**: Firebase FCM + Mailgun (Email)

## 📦 المتطلبات

- PHP >= 8.2
- Composer
- PostgreSQL
- Redis
- Node.js & NPM

## 🔧 التثبيت

1. **استنساخ المستودع**
```bash
git clone <repository-url>
cd backend
```

2. **تثبيت الحزم**
```bash
composer install
```

3. **إعداد ملف البيئة**
```bash
cp .env.example .env
php artisan key:generate
```

4. **تعديل ملف `.env`**
```env
APP_NAME="Clinic Management System"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=clinic_management
DB_USERNAME=your_username
DB_PASSWORD=your_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

5. **تشغيل الترحيلات**
```bash
php artisan migrate
```

6. **تشغيل الخادم**
```bash
php artisan serve
```

## 🧪 الاختبارات

تشغيل جميع الاختبارات:
```bash
php artisan test
```

تشغيل اختبار محدد:
```bash
php artisan test --filter ExampleTest
```

## 📚 وثائق API

لتوليد وثائق API:
```bash
php artisan scribe:generate
```

## 🔐 الأدوار والصلاحيات

- **Super Admin**: إدارة العيادات، الباقات، المشتركين، إعدادات النظام الكاملة
- **Clinic Manager**: إدارة الأقسام، الأطباء، جداول العمل، التقارير
- **Doctor**: عرض المواعيد، السجل الطبي، إضافة تشخيصات ووصفات
- **Receptionist**: إدارة المواعيد اليومية، تسجيل حضور المرضى
- **Patient**: حجز المواعيد، عرض السجل الطبي، تاريخ الزيارات

## 🗂️ بنية المشروع

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Services/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
└── tests/
```

## 🔄 أوامر Artisan المفيدة

```bash
# إنشاء نموذج جديد
php artisan make:model ModelName -m

# إنشاء تحكم
php artisan make:controller ControllerName

# إنشاء طلب تحقق
php artisan make:request RequestName

# مسح الكاش
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📝 المساهمة

للمساهمة في المشروع، يرجى:
1. عمل Fork للمستودع
2. إنشاء فرع للميزة (`git checkout -b feature/AmazingFeature`)
3. Commit التغييرات (`git commit -m 'Add some AmazingFeature'`)
4. Push إلى الفرع (`git push origin feature/AmazingFeature`)
5. فتح Pull Request

## 📄 الترخيص

هذا المشروع مرخص تحت ترخيص MIT - راجع ملف LICENSE للتفاصيل.

## 📞 الدعم

لأي استفسارات أو دعم، يرجى التواصل مع فريق التطوير.
