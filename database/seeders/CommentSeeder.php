<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
{
    public function run()
    {
        // Очищаем таблицу комментариев
        DB::table('comments')->truncate();

        // Создаём 40 тестовых комментариев
        Comment::factory()->count(40)->create();
    }
}
