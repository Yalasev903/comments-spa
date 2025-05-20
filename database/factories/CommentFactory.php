<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = \App\Models\Comment::class;

    public function definition(): array
    {
        return [
            'user_name' => $this->faker->userName(),
            'email'     => $this->faker->unique()->safeEmail(),
            'text'      => $this->faker->realText(80),
            'home_page' => $this->faker->optional()->url(),
            'parent_id' => null, // или можно позже добавить вложенные
            // 'attachment_path' и другие поля — опционально
        ];
    }
}
