<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FeedbackSeeder extends Seeder
{
    /**
     * Seed 5 users, each with 5 posts.
     * Every other user votes on every post (except their own),
     * producing varied vote totals across all posts.
     */
    public function run(): void
    {
        $users = collect([
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com'],
            ['name' => 'Bob Martinez', 'email' => 'bob@example.com'],
            ['name' => 'Carol Williams', 'email' => 'carol@example.com'],
            ['name' => 'David Lee', 'email' => 'david@example.com'],
            ['name' => 'Eva Chen', 'email' => 'eva@example.com'],
        ])->map(fn($data) => User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            ));

        $postData = [
            ['title' => 'Add dark mode support to the dashboard', 'category' => 'feature-request'],
            ['title' => 'Allow users to export their data as CSV', 'category' => 'feature-request'],
            ['title' => 'Improve search result ranking algorithm', 'category' => 'improvement'],
            ['title' => 'Support markdown in post descriptions', 'category' => 'feature-request'],
            ['title' => 'Show post author avatar in the listing page', 'category' => 'improvement'],

            ['title' => 'Login page shows blank screen on mobile', 'category' => 'bug-report'],
            ['title' => 'Pagination breaks when filtering by category', 'category' => 'bug-report'],
            ['title' => 'How do I reset my password without email?', 'category' => 'question'],
            ['title' => 'Vote count does not update after refresh', 'category' => 'bug-report'],
            ['title' => 'API returns 500 on empty description field', 'category' => 'bug-report'],

            ['title' => 'Add keyboard shortcuts for common actions', 'category' => 'feature-request'],
            ['title' => 'Notify users when someone votes on their post', 'category' => 'improvement'],
            ['title' => 'Allow editing post category after publishing', 'category' => 'improvement'],
            ['title' => 'Weekly digest email of top posts', 'category' => 'feature-request'],
            ['title' => 'General discussion: best category structure?', 'category' => 'general'],

            ['title' => 'Implement post tagging system', 'category' => 'feature-request'],
            ['title' => 'Add comment threads under each post', 'category' => 'feature-request'],
            ['title' => 'Enable pinning important posts to the top', 'category' => 'feature-request'],
            ['title' => 'Show trending posts on the home page', 'category' => 'improvement'],
            ['title' => 'Allow anonymous post submissions', 'category' => 'feature-request'],

            ['title' => 'Two-factor authentication support', 'category' => 'feature-request'],
            ['title' => 'Captcha on registration to reduce spam', 'category' => 'improvement'],
            ['title' => 'Why are votes limited per minute?', 'category' => 'question'],
            ['title' => 'Add a changelog section to the platform', 'category' => 'feature-request'],
            ['title' => 'Option to follow specific categories', 'category' => 'feature-request'],
        ];

        $posts = collect();
        $users->each(function (User $user, int $userIndex) use (&$posts, $postData) {
            $slice = array_slice($postData, $userIndex * 5, 5);
            foreach ($slice as $data) {
                $post = Post::firstOrCreate(
                    ['slug' => Str::slug($data['title'])],
                    [
                        'user_id' => $user->id,
                        'title' => $data['title'],
                        'description' => fake()->paragraph(3),
                        'category' => $data['category'],
                    ]
                );
                $posts->push($post);
            }
        });

        $votingMatrix = [
            0 => [5 => 1, 6 => -1, 7 => 1, 8 => -1, 9 => 1, 10 => -1, 11 => -1, 12 => -1, 13 => 1, 14 => -1, 15 => 1, 16 => -1, 17 => 1, 18 => -1, 19 => 1, 20 => 1, 21 => 1, 22 => 1, 23 => 1, 24 => 1],
            1 => [0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 10 => -1, 11 => -1, 12 => 1, 13 => -1, 14 => -1, 15 => 1, 16 => 1, 17 => -1, 18 => 1, 19 => -1, 20 => 1, 21 => 1, 22 => -1, 23 => 1, 24 => 1],
            2 => [0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => -1, 5 => 1, 6 => -1, 7 => 1, 8 => 1, 9 => -1, 15 => -1, 16 => 1, 17 => 1, 18 => -1, 19 => 1, 20 => 1, 21 => 1, 22 => 1, 23 => 1, 24 => 1],
            3 => [0 => 1, 1 => 1, 2 => -1, 3 => 1, 4 => 1, 5 => -1, 6 => 1, 7 => -1, 8 => 1, 9 => -1, 10 => -1, 11 => -1, 12 => 1, 13 => 1, 14 => -1, 20 => 1, 21 => -1, 22 => 1, 23 => 1, 24 => 1],
            4 => [0 => 1, 1 => 1, 2 => 1, 3 => -1, 4 => 1, 5 => 1, 6 => -1, 7 => 1, 8 => -1, 9 => 1, 10 => -1, 11 => -1, 12 => -1, 13 => 1, 14 => -1, 15 => 1, 16 => -1, 17 => 1, 18 => 1, 19 => -1],
        ];

        foreach ($votingMatrix as $voterIndex => $voteMap) {
            $voter = $users[$voterIndex];
            foreach ($voteMap as $postIndex => $type) {
                $post = $posts[$postIndex] ?? null;
                if (!$post) {
                    continue;
                }
                // Skip if this user owns the post
                if ($post->user_id === $voter->id) {
                    continue;
                }
                Vote::updateOrCreate(
                    ['user_id' => $voter->id, 'post_id' => $post->id],
                    ['type' => $type]
                );
            }
        }

        $this->command->info('FeedbackSeeder: 5 users, 25 posts, and varied votes created.');
        $this->command->table(
            ['User', 'Email', 'Password'],
            $users->map(fn($u) => [$u->name, $u->email, 'password'])->toArray()
        );
    }
}
