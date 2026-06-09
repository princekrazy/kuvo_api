#!/bin/sh

php artisan reverb:start &
php artisan serve --host=0.0.0.0 --port=$PORT