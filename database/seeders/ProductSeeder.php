<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Electricals
            ['category' => 'Electricals', 'name' => 'Stand Mixer', 'price' => 299.99, 'sale_price' => 249.99, 'description' => 'Professional stand mixer with 5 speed settings and planetary mixing action.', 'features' => '800W motor, 5L stainless steel bowl, Multiple attachments included', 'stock' => 25, 'is_featured' => true],
            ['category' => 'Electricals', 'name' => 'Food Processor', 'price' => 149.99, 'description' => 'Multi-function food processor for chopping, slicing, and blending.', 'features' => '600W, 12-cup capacity, Safety lid lock', 'stock' => 18],
            ['category' => 'Electricals', 'name' => 'Electric Kettle', 'price' => 49.99, 'sale_price' => 39.99, 'description' => 'Fast-boil kettle with temperature control.', 'features' => '1.7L capacity, Rapid boil, Auto shut-off', 'stock' => 45, 'is_featured' => true],
            
            // Cookware
            ['category' => 'Cookware', 'name' => 'Non-Stick Frying Pan Set', 'price' => 89.99, 'sale_price' => 69.99, 'description' => 'Set of 3 non-stick frying pans in different sizes.', 'features' => 'PFOA-free coating, Induction compatible, Oven safe to 180°C', 'stock' => 32, 'is_featured' => true],
            ['category' => 'Cookware', 'name' => 'Cast Iron Dutch Oven', 'price' => 129.99, 'description' => 'Heavy-duty cast iron pot perfect for slow cooking.', 'features' => '5.5L capacity, Enamel coating, Suitable for all hobs', 'stock' => 20],
            ['category' => 'Cookware', 'name' => 'Stainless Steel Saucepan Set', 'price' => 159.99, 'description' => 'Premium stainless steel saucepans with copper base.', 'features' => 'Set of 3, Tri-ply construction, Dishwasher safe', 'stock' => 28],
            
            // Bakeware
            ['category' => 'Bakeware', 'name' => 'Baking Tray Set', 'price' => 34.99, 'sale_price' => 24.99, 'description' => 'Non-stick baking trays for perfect results every time.', 'features' => 'Set of 3, Carbon steel, Non-stick coating', 'stock' => 50, 'is_featured' => true],
            ['category' => 'Bakeware', 'name' => 'Cake Tin Set', 'price' => 44.99, 'description' => 'Round cake tins with loose bottoms.', 'features' => '3 sizes: 8", 9", 10", Spring-form design', 'stock' => 35],
            ['category' => 'Bakeware', 'name' => 'Silicone Baking Mat Set', 'price' => 19.99, 'description' => 'Reusable silicone baking mats.', 'features' => 'Set of 2, Heat resistant to 230°C, Eco-friendly', 'stock' => 60],
            
            // Knives
            ['category' => 'Knives', 'name' => 'Professional Chef\'s Knife', 'price' => 79.99, 'sale_price' => 59.99, 'description' => 'German steel chef\'s knife with superior edge retention.', 'features' => '8-inch blade, Ergonomic handle, Full tang construction', 'stock' => 40, 'is_featured' => true],
            ['category' => 'Knives', 'name' => 'Knife Block Set', 'price' => 199.99, 'sale_price' => 159.99, 'description' => 'Complete 7-piece knife set with wooden block.', 'features' => 'High-carbon steel, Lifetime warranty, Professional grade', 'stock' => 15, 'is_featured' => true],
            ['category' => 'Knives', 'name' => 'Paring Knife Set', 'price' => 29.99, 'description' => 'Set of 3 paring knives for precision work.', 'features' => 'Sharp edges, Comfortable grip, Multi-purpose', 'stock' => 55],
            
            // Tableware
            ['category' => 'Tableware', 'name' => 'Porcelain Dinner Set', 'price' => 129.99, 'description' => '16-piece dinner set for 4 people.', 'features' => 'Dishwasher safe, Chip resistant, Classic white', 'stock' => 22],
            ['category' => 'Tableware', 'name' => 'Slate Serving Boards', 'price' => 39.99, 'sale_price' => 29.99, 'description' => 'Natural slate boards perfect for entertaining.', 'features' => 'Set of 2, Unique appearance, Easy to clean', 'stock' => 38],
            ['category' => 'Tableware', 'name' => 'Cutlery Set', 'price' => 59.99, 'description' => '24-piece stainless steel cutlery set.', 'features' => 'Service for 6, Mirror finish, Dishwasher safe', 'stock' => 30],
            
            // Drinkware
            ['category' => 'Drinkware', 'name' => 'Wine Glass Set', 'price' => 44.99, 'description' => 'Crystal wine glasses for red and white wine.', 'features' => 'Set of 6, Crystal glass, Elegant design', 'stock' => 42],
            ['category' => 'Drinkware', 'name' => 'Insulated Travel Mug', 'price' => 24.99, 'sale_price' => 19.99, 'description' => 'Keep beverages hot or cold for hours.', 'features' => '480ml capacity, Leak-proof lid, BPA-free', 'stock' => 65, 'is_featured' => true],
            ['category' => 'Drinkware', 'name' => 'Coffee Mug Set', 'price' => 29.99, 'description' => 'Ceramic coffee mugs in assorted colors.', 'features' => 'Set of 4, 350ml each, Microwave safe', 'stock' => 48],
            
            // Accessories
            ['category' => 'Accessories', 'name' => 'Silicone Utensil Set', 'price' => 34.99, 'description' => 'Heat-resistant silicone kitchen utensils.', 'features' => '6-piece set, Non-scratch, Heat resistant to 230°C', 'stock' => 52],
            ['category' => 'Accessories', 'name' => 'Bamboo Cutting Board', 'price' => 24.99, 'description' => 'Eco-friendly bamboo cutting board.', 'features' => 'Large size, Knife-friendly, With juice groove', 'stock' => 45],
            ['category' => 'Accessories', 'name' => 'Kitchen Scale', 'price' => 39.99, 'description' => 'Digital kitchen scale with precise measurements.', 'features' => '5kg capacity, Tare function, LCD display', 'stock' => 35],
        ];

        foreach ($products as $productData) {
            $category = Category::where('name', $productData['category'])->first();
            
            if ($category) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'description' => $productData['description'],
                    'features' => $productData['features'],
                    'price' => $productData['price'],
                    'sale_price' => $productData['sale_price'] ?? null,
                    'sku' => 'PRO-' . strtoupper(Str::random(8)),
                    'stock' => $productData['stock'],
                    'is_featured' => $productData['is_featured'] ?? false,
                    'is_active' => true,
                ]);
            }
        }
    }
}
