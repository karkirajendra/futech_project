<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have at least one known user
        $user = User::first() ?? User::factory()->create([
            'name' => 'Blog User',
            'email' => 'blog@example.com',
        ]);

        // Create some blogs for that user
        Blog::factory()
            ->count(5)
            ->for($user)
            ->create();
    }
}


