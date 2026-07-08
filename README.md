# Laravel Hospital Website — MediCare

## Requirements
- PHP: 8.1 or higher
- Composer: Latest version
- Laravel: 10 or higher
- MySQL: 5.7 or higher
- Node.js (optional for frontend assets)

---

## Features

### Frontend
- Dynamic homepage with hero slider, about section, counter up, services, blog, testimonials
- Doctors listing page with detail page
- Blog listing with category and tag filtering
- Blog detail page with comments, recent posts, social share
- Appointment booking form (select doctor, date, time, visit type)
- Contact page contact form (sent to email)
- About page

### Admin Panel (CMS)
- Secure admin login and logout
- General settings (site name, logo, favicon, header address, working hours, social links, footer)
- Home page settings (about section, counter up numbers)
- Slider management (add, edit, delete — full CRUD)
- Doctor management (add, edit, delete — full CRUD)
- Blog management (add, edit, delete — full CRUD with Summernote rich text editor)
- Blog categories management
- Blog tags management
- Blog comments management (approve / delete)
- Appointment management (view all appointments with status)

---

## Installation Steps

### Clone Project
```
git clone https://github.com/Risad212/Medicare.git
cd your-project
```

### Install Dependencies
```
composer install
```

### Create Environment File
```
cp .env.example .env
```

### Generate App Key
```
php artisan key:generate
```

### Configure Database (.env)
```
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=
```

### Run Migrations
```
php artisan migrate
```

### Create Storage Link
```
php artisan storage:link
```

### Run Project
```
php artisan serve
```

---

## Open Project
```
http://127.0.0.1:8000
```

## Admin Panel
```
http://127.0.0.1:8000/admin
```

---

## Important Notes
- Use PHP 8.1+
- Keep assets inside public/ folder
- Always use asset() helper for CSS, JS, images
- File names are case-sensitive
- Configure .env properly before running project
- Run `php artisan storage:link` for image uploads to work

---

## Useful Commands
```
php artisan route:list
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan migrate:fresh
php artisan storage:link
```

---

## Done
Project is ready to run 🚀
