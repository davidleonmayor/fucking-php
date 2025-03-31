# Workflow

1. `composer install`
2. `cp .env.example .env`
3. `php artisan key:generate`
4. `npm install`
5. `npm run dev`
6. `php artisan migrate --seed` or clear database and refill `php artisan migrate:fresh --seed`
7. `php artisan serve`

```bash
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

# TODO

1. validate GenreController and GenreControllercopy
