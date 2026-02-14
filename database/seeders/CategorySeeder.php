<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Electricals', 'icon' => '⚡', 'order' => 1],
            ['name' => 'Cookware', 'icon' => '🍳', 'order' => 2],
            ['name' => 'Bakeware', 'icon' => '🧁', 'order' => 3],
            ['name' => 'Knives', 'icon' => '🔪', 'order' => 4],
            ['name' => 'Tableware', 'icon' => '🍽️', 'order' => 5],
            ['name' => 'Drinkware', 'icon' => '☕', 'order' => 6],
            ['name' => 'Accessories', 'icon' => '🔧', 'order' => 7],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'icon' => $category['icon'],
                'description' => 'Premium ' . $category['name'] . ' for your kitchen',
                'order' => $category['order'],
            ]);
        }
    }
}
