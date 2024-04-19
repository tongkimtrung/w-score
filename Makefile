setup:
	@make build
	@make up
	@make composer
	@make data

build:
	docker compose build --no-cache --force-rm
up:
	docker compose up -d
composer:
	docker exec w-backend bash -c "composer update"
	docker exec w-backend bash -c "composer i"
data:
	docker exec w-backend bash -c "php artisan migrate:rollback"
	docker exec w-backend bash -c "php artisan migrate"
	docker exec w-backend bash -c "php artisan db:seed"
