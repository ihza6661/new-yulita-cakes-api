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
                'category_name' => 'Kue Kering',
                'image'         => public_path('yulita-cakes/kue-kering.jpg'),
            ],
            [
                'category_name' => 'Kue Basah',
                'image'       => public_path('yulita-cakes/kue-basah.jpg'),
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
