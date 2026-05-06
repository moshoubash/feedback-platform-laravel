<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    private static array $categories = [
        'feature-request',
        'bug-report',
        'improvement',
        'question',
        'general',
    ];

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(6, true);

        return [
            'user_id'     => User::factory(),
            'title'       => $title,
            'slug'        => Str::slug($title),
            'description' => $this->faker->paragraph(3),
            'category'    => $this->faker->randomElement(self::$categories),
        ];
    }
}
