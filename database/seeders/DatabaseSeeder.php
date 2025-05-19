<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Очищаем таблицу пользователей (безопасно для тестовой БД!)
        DB::table('users')->truncate();

        // Создаём 10 уникальных пользователей
        for ($i = 1; $i <= 10; $i++) {
            User::factory()->create([
                'name' => 'Test User ' . $i,
                'email' => "testuser{$i}@example.com",
            ]);
        }

        // Также запускаем сидер комментариев (см. ниже)
        $this->call(CommentSeeder::class);
    }
}
