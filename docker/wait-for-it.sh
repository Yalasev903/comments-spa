#!/usr/bin/env bash

host="$1"
shift
port="$1"
shift

echo "⏳ Ждём $host:$port..."
while ! nc -z "$host" "$port"; do
  sleep 1
done

echo "✅ $host:$port доступен. Запуск..."
exec "$@"
