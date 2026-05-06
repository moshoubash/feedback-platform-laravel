<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    protected $model = Vote::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'type'    => $this->faker->randomElement([1, -1]),
        ];
    }

    /** Force an upvote */
    public function upvote(): static
    {
        return $this->state(['type' => 1]);
    }

    /** Force a downvote */
    public function downvote(): static
    {
        return $this->state(['type' => -1]);
    }
}
