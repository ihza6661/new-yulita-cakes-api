<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Celana',
                'image'         => public_path('product-as-denim/Indigo-slub-workshirt-LS-1.jpg'),
            ],
            [
                'category_name' => 'Kemeja',
                'image'       => public_path('product-as-denim/Katalog-GAMA-1-2000x2500.jpg'),
            ],
            [
                'category_name' => 'Kaos',
                'image'       => public_path('product-as-denim/Yasuke-COVER-1-1.jpg'),
            ],
            [
                'category_name' => 'Sepatu',
                'image'       => public_path('product-as-denim/Tshirt-Duck-white-1.jpg'),
            ],
        ];

        foreach ($categories as $category) {
            if (file_exists($category['image'])) {
                $filename = 'categories/' . Str::random(20) . '.' . pathinfo($category['image'], PATHINFO_EXTENSION);

                Storage::disk('public')->put($filename, file_get_contents($category['image']));

                $category['image'] = $filename;
            } else {
                $category['image'] = 'categories/default.jpg';
            }

            Category::create($category);
        }
    }
}
