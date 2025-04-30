<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


# 🚀 Cara Menjalankan API di Local

## 1. Clone Repository
```
git clone https://github.com/ammrbhlwn/backend-silapor.git
```

## 2. Install Composer
```
composer install
```

## 3. Setup Database
- ### Buat file .env
```
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:Jc+2f6qhO58vnZxhk3RVl+9gxpaQf0fFwSO0LwBTAaw=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_URL=postgresql://postgres:si-lapor-database@db.jynmzifknyokgtbzddsr.supabase.co:5432/postgres
DB_PASSWORD=si-lapor-database

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
```

## 4. Run API via Postman
- ### Buka link tersebut
    **[SiLapor API Documentation](https://documenter.getpostman.com/view/39302183/2sB2izEZAv)**
    
- ### Click Run in Postman

- ### Setup Variable
    - Set variable sebagai berikut:

        #### 1. Base Url
            Variable        ➡️ base_url
            Initial value   ➡️ http://127.0.0.1:8000/api
            Current value   ➡️ http://127.0.0.1:8000/api

         #### 2. Token User
            Variable        ➡️ token_user
            Initial value   ➡️ (null)
            Current value   ➡️ (null)

        #### 3. Token Pengelola
            Variable        ➡️ token_pengelola
            Initial value   ➡️ (null)
            Current value   ➡️ (null)
    
    Note: Token user dan pengelola didapatkan ketika melakukan login. Setelah mendapatkan token, simpan ke initial value dan current token untuk masing-masing variable

## 5. Jalankan Server
```
php artisan serve
```

## 6. Send Request via Postman
