#!/bin/sh

composer update
composer i
php artisan migrate:fresh --seed
