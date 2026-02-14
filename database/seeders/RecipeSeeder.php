<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        // Recipe 1: Classic Spaghetti Carbonara
        $recipe1 = Recipe::create([
            'user_id' => $users->random()->id,
            'title' => 'Classic Spaghetti Carbonara',
            'short_description' => 'A traditional Italian pasta dish with eggs, cheese, and pancetta. Creamy, rich, and absolutely delicious!',
            'cuisine_type' => 'Italian',
            'category' => 'Main Course',
            'prep_time' => 10,
            'cook_time' => 20,
            'total_time' => 30,
            'serving_size' => 4,
            'preparation_notes' => "1. Cook spaghetti according to package instructions until al dente.\n2. While pasta cooks, fry pancetta until crispy.\n3. In a bowl, whisk together eggs, Parmesan, and black pepper.\n4. Drain pasta, reserving 1 cup of pasta water.\n5. Add hot pasta to pancetta, remove from heat.\n6. Quickly stir in egg mixture, adding pasta water to create a creamy sauce.\n7. Serve immediately with extra Parmesan and black pepper.",
        ]);

        $recipe1->ingredients()->createMany([
            ['name' => 'Spaghetti', 'measurement' => '400g', 'order' => 1],
            ['name' => 'Pancetta', 'measurement' => '200g, diced', 'order' => 2, 'substitution_option' => 'Bacon'],
            ['name' => 'Eggs', 'measurement' => '4 large', 'order' => 3, 'allergen_info' => 'Contains eggs'],
            ['name' => 'Parmesan Cheese', 'measurement' => '100g, grated', 'order' => 4, 'allergen_info' => 'Contains dairy'],
            ['name' => 'Black Pepper', 'measurement' => '2 tsp, freshly ground', 'order' => 5],
            ['name' => 'Salt', 'measurement' => 'To taste', 'order' => 6],
        ]);

        // Recipe 2: Chicken Tikka Masala
        $recipe2 = Recipe::create([
            'user_id' => $users->random()->id,
            'title' => 'Chicken Tikka Masala',
            'short_description' => 'Tender marinated chicken in a creamy tomato-based curry sauce. A British-Indian favorite that\'s perfect with naan or rice.',
            'cuisine_type' => 'Indian',
            'category' => 'Main Course',
            'prep_time' => 30,
            'cook_time' => 40,
            'total_time' => 70,
            'serving_size' => 6,
            'preparation_notes' => "1. Marinate chicken in yogurt and spices for at least 30 minutes.\n2. Grill or pan-fry chicken until cooked through.\n3. In a large pan, sauté onions, garlic, and ginger.\n4. Add tomato sauce, cream, and spices.\n5. Simmer sauce for 15 minutes.\n6. Add cooked chicken and simmer for 10 more minutes.\n7. Garnish with cilantro and serve with rice or naan.",
        ]);

        $recipe2->ingredients()->createMany([
            ['name' => 'Chicken Breast', 'measurement' => '800g, cubed', 'order' => 1],
            ['name' => 'Yogurt', 'measurement' => '200ml', 'order' => 2, 'allergen_info' => 'Contains dairy', 'substitution_option' => 'Coconut yogurt for dairy-free'],
            ['name' => 'Heavy Cream', 'measurement' => '150ml', 'order' => 3, 'allergen_info' => 'Contains dairy'],
            ['name' => 'Tomato Sauce', 'measurement' => '400g can', 'order' => 4],
            ['name' => 'Onion', 'measurement' => '2 large, chopped', 'order' => 5],
            ['name' => 'Garlic', 'measurement' => '4 cloves, minced', 'order' => 6],
            ['name' => 'Ginger', 'measurement' => '2 tbsp, grated', 'order' => 7],
            ['name' => 'Garam Masala', 'measurement' => '2 tbsp', 'order' => 8],
            ['name' => 'Cumin', 'measurement' => '1 tbsp', 'order' => 9],
            ['name' => 'Paprika', 'measurement' => '1 tbsp', 'order' => 10],
            ['name' => 'Cilantro', 'measurement' => 'Fresh, for garnish', 'order' => 11],
        ]);

        // Recipe 3: Chocolate Lava Cake
        $recipe3 = Recipe::create([
            'user_id' => $users->random()->id,
            'title' => 'Chocolate Lava Cake',
            'short_description' => 'Individual chocolate cakes with a molten chocolate center. Decadent and impressive dessert that\'s easier to make than you think!',
            'cuisine_type' => 'French',
            'category' => 'Dessert',
            'prep_time' => 15,
            'cook_time' => 12,
            'total_time' => 27,
            'serving_size' => 4,
            'preparation_notes' => "1. Preheat oven to 220°C (425°F).\n2. Butter and flour 4 ramekins.\n3. Melt chocolate and butter together.\n4. Whisk eggs and sugar until thick and pale.\n5. Fold melted chocolate into egg mixture.\n6. Sift in flour and gently fold.\n7. Divide batter among ramekins.\n8. Bake for 12 minutes until edges are firm but center is soft.\n9. Let stand 1 minute, then invert onto plates.\n10. Serve immediately with ice cream or whipped cream.",
        ]);

        $recipe3->ingredients()->createMany([
            ['name' => 'Dark Chocolate', 'measurement' => '200g, chopped', 'order' => 1, 'allergen_info' => 'May contain traces of nuts'],
            ['name' => 'Butter', 'measurement' => '100g', 'order' => 2, 'allergen_info' => 'Contains dairy'],
            ['name' => 'Eggs', 'measurement' => '4 large', 'order' => 3, 'allergen_info' => 'Contains eggs'],
            ['name' => 'Sugar', 'measurement' => '100g', 'order' => 4],
            ['name' => 'All-Purpose Flour', 'measurement' => '50g', 'order' => 5, 'allergen_info' => 'Contains gluten'],
            ['name' => 'Vanilla Extract', 'measurement' => '1 tsp', 'order' => 6],
        ]);

        // Recipe 4: Greek Salad
        $recipe4 = Recipe::create([
            'user_id' => $users->random()->id,
            'title' => 'Authentic Greek Salad',
            'short_description' => 'Fresh and vibrant Mediterranean salad with tomatoes, cucumbers, feta cheese, and olives. Perfect for summer!',
            'cuisine_type' => 'Greek',
            'category' => 'Salad',
            'prep_time' => 15,
            'cook_time' => 0,
            'total_time' => 15,
            'serving_size' => 4,
            'preparation_notes' => "1. Cut tomatoes and cucumbers into large chunks.\n2. Slice red onion thinly.\n3. Combine vegetables in a large bowl.\n4. Add olives and feta cheese.\n5. Drizzle with olive oil and red wine vinegar.\n6. Season with oregano, salt, and pepper.\n7. Toss gently and serve immediately.",
        ]);

        $recipe4->ingredients()->createMany([
            ['name' => 'Tomatoes', 'measurement' => '4 large, chunked', 'order' => 1],
            ['name' => 'Cucumber', 'measurement' => '1 large, chunked', 'order' => 2],
            ['name' => 'Red Onion', 'measurement' => '1 medium, sliced', 'order' => 3],
            ['name' => 'Feta Cheese', 'measurement' => '200g, cubed', 'order' => 4, 'allergen_info' => 'Contains dairy'],
            ['name' => 'Kalamata Olives', 'measurement' => '100g', 'order' => 5],
            ['name' => 'Extra Virgin Olive Oil', 'measurement' => '4 tbsp', 'order' => 6],
            ['name' => 'Red Wine Vinegar', 'measurement' => '2 tbsp', 'order' => 7],
            ['name' => 'Dried Oregano', 'measurement' => '1 tsp', 'order' => 8],
            ['name' => 'Salt and Pepper', 'measurement' => 'To taste', 'order' => 9],
        ]);

        // Recipe 5: Beef Tacos
        $recipe5 = Recipe::create([
            'user_id' => $users->random()->id,
            'title' => 'Spicy Beef Tacos',
            'short_description' => 'Quick and easy Mexican-style tacos with seasoned ground beef and fresh toppings. Perfect for a weeknight dinner!',
            'cuisine_type' => 'Mexican',
            'category' => 'Main Course',
            'prep_time' => 10,
            'cook_time' => 15,
            'total_time' => 25,
            'serving_size' => 4,
            'preparation_notes' => "1. Brown ground beef in a large skillet.\n2. Add taco seasoning and water, simmer until thickened.\n3. Warm taco shells according to package instructions.\n4. Fill shells with seasoned beef.\n5. Top with shredded lettuce, diced tomatoes, cheese, and sour cream.\n6. Add hot sauce if desired.\n7. Serve immediately with lime wedges.",
        ]);

        $recipe5->ingredients()->createMany([
            ['name' => 'Ground Beef', 'measurement' => '500g', 'order' => 1, 'substitution_option' => 'Ground turkey or plant-based meat'],
            ['name' => 'Taco Seasoning', 'measurement' => '2 tbsp', 'order' => 2],
            ['name' => 'Taco Shells', 'measurement' => '8-12 shells', 'order' => 3, 'allergen_info' => 'May contain gluten'],
            ['name' => 'Lettuce', 'measurement' => '2 cups, shredded', 'order' => 4],
            ['name' => 'Tomatoes', 'measurement' => '2 medium, diced', 'order' => 5],
            ['name' => 'Cheddar Cheese', 'measurement' => '1 cup, shredded', 'order' => 6, 'allergen_info' => 'Contains dairy'],
            ['name' => 'Sour Cream', 'measurement' => '1/2 cup', 'order' => 7, 'allergen_info' => 'Contains dairy'],
            ['name' => 'Lime', 'measurement' => '1, cut into wedges', 'order' => 8],
        ]);
    }
}
