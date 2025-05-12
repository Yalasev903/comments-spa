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
```

---

## ✅ Дополнительно

- Поддержка вложенности неограниченной глубины (рекурсивная модель)
- Валидация на клиенте и сервере
- Поддержка captcha
- Защищённая загрузка и скачивание файлов
- Swagger без аннотаций — через YAML
