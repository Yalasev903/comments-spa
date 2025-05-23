# 🧠 SPA "Комментарии" (Laravel 12 + Vue 3)

Полноценное приложение для древовидных комментариев с вложениями, XSS-защитой, Docker-окружением, Swagger-документацией и REST API.

---

## 🚀 Запуск проекта

```bash
docker compose down -v
docker compose up --build
```

После запуска:

- 🌐 Приложение: http://localhost:8080
- 📚 Swagger UI: http://localhost:8080/api/documentation

---

## ⚙️ Стек

- Laravel 12 (PHP 8.4)
- Vue 3 + Vite + Bootstrap
- Docker + MySQL
- Swagger (YAML)
- Очереди, события, вложения
- Безопасность: XSS + SQL-инъекции + валидация

---

## 📁 Структура

| Каталог / файл            | Назначение |
|---------------------------|------------|
| `app/Http/Controllers/Api/CommentController.php` | API для комментариев |
| `resources/js/components/` | Vue-компоненты |
| `docs/openapi.yaml`       | OpenAPI-описание |
| `storage/api-docs/api-docs.yaml` | Файл для Swagger |
| `config/l5-swagger.php`   | Конфигурация Swagger |
| `.env.example`            | Переменные окружения |

---

## 🧪 API Эндпоинты

| Метод | URL                         | Описание                    |
|-------|-----------------------------|-----------------------------|
| GET   | `/api/comments`             | Получить все комментарии   |
| POST  | `/api/comments`             | Добавить новый комментарий |
| GET   | `/api/comments/{id}/download` | Скачать вложение файла     |

Подробное описание: **[Swagger UI](http://localhost:8080/api/documentation)**

---

## 🛠️ Команды внутри контейнера

```bash
# Подключение в контейнер
docker exec -it comments-spa-app bash

# Миграции
php artisan migrate

# Линки для storage
php artisan storage:link

# Очистка кэша
php artisan cache:clear

# Запуск очереди вручную
php artisan queue:work
```

---

## ✅ Дополнительно

- Поддержка вложенности неограниченной глубины (рекурсивная модель)
- Валидация на клиенте и сервере
- Поддержка captcha
- Защищённая загрузка и скачивание файлов
- Swagger без аннотаций — через YAML

---

## 📦 Тестирование
Feature-тесты (Feature/CommentApiTest.php):

```bash
docker-compose exec app php artisan test
```
Проверка получения комментариев и дерева

Проверка создания и валидации комментария

Проверка вложений

Проверка вложенных ответов (reply)

Проверка событий и очередей

---

## 🛠️ Makefile — быстрые команды для разработки

В проекте есть Makefile для упрощения всех типовых действий:

|Команда       | Описание                                          |
|--------------|---------------------------------------------------|
| make up 	| Запуск и билд Docker-контейнеров                  |
|make down	| Остановка и удаление контейнеров с volume         |
|make bash	| Подключение внутрь контейнера приложения          |
|make migrate	| Полная миграция и наполнение тестовыми данными    |
|make link	| Линковка storage (Laravel)                        |
|make cache	|Очистка и пересборка конфиг-кеша, маршрутов        |
|make test-comment	|Прогон только теста API комментариев          |
|make swagger	|Генерация Swagger-документации                     |
|make fix-perms	|Быстрое исправление прав на storage/bootstrap |
```bash
make up           # Запустить проект
make bash         # Подключиться в контейнер
make migrate      # Пересоздать БД с сидерами
make test-comment # Проверить только CommentApiTest
make fix-perms    # Если возникают ошибки прав (permission denied), используйте:
```
---

## 🧩 Кратко о возможностях
Неограниченная вложенность (рекурсивное дерево)

Сортировка, пагинация, предпросмотр, фильтрация по тегам

Загрузка файлов (JPG, PNG, GIF, TXT до 320x240px или 100Кб)

Эффекты (анимации, плавное появление)

Панель форматирования текста

Кэширование и быстрый отклик

WebSocket-оповещения (реальное время)

Swagger/OpenAPI-документация

Docker-first инфраструктура

