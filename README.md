# Laravel Project Setup Guide

## Requirements
- PHP: 8.1 or higher
- Composer: Latest version
- Laravel: 10 or higher
- MySQL: 5.7 or higher
- Node.js (optional for frontend assets)

## Installation Steps

### Clone Project
git clone https://your-repo-url.git
cd your-project

### Install Dependencies
composer install

### Create Environment File
cp .env.example .env

### Generate App Key
php artisan key:generate

### Configure Database (.env)
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=

### Run Migrations (if needed)
php artisan migrate

### Create Storage Link
php artisan storage:link

### Run Project
php artisan serve

## Open Project
http://127.0.0.1:8000

## Important Notes
- Use PHP 8.1+
- Keep assets inside public/ folder
- Always use asset() helper for CSS, JS, images
- File names are case-sensitive
- Configure .env properly before running project

## Useful Commands
php artisan route:list
php artisan config:clear
php artisan cache:clear
php artisan view:clear

## Done
Project is ready to run 🚀
