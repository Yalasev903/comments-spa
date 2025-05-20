# Имя контейнера
APP = comments-spa-app

# Запуск Docker и билд
up:
	docker compose up --build -d

# Остановка и удаление
down:
	docker compose down -v

# Подключение в контейнер
bash:
	docker exec -it $(APP) bash

# Очистка и повторная миграция
migrate:
	docker exec -it $(APP) php artisan migrate:fresh --seed

# Линк storage
link:
	docker exec -it $(APP) php artisan storage:link

# Очистка кеша
cache:
	docker exec -it $(APP) php artisan config:clear && \
	docker exec -it $(APP) php artisan config:cache && \
	docker exec -it $(APP) php artisan route:clear

# Запуск всех тестов с подробным выводом
test-all:
	docker exec -it $(APP) php artisan test --verbose

# Только один тест
test-comment:
	docker exec -it $(APP) php artisan test --filter=CommentApiTest

# Swagger генерация (если l5-swagger установлен)
swagger:
	docker exec -it $(APP) php artisan l5-swagger:generate

# Починка прав доступа (если permission denied)
fix-perms:
	sudo chown -R $$(id -u):$$(id -g) storage bootstrap
	sudo chmod -R 775 storage bootstrap/cache
