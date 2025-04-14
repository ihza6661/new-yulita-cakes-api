<?php

namespace Database\Seeders; // Pastikan namespace sesuai dengan struktur folder Anda

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // Tambahkan ini
use Illuminate\Support\Facades\Http;    // Tambahkan ini
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan; // Tambahkan ini
use Faker\Factory as Faker; // Pastikan Faker di-import

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void // Tambahkan tipe return void
    {
        $faker = Faker::create('id_ID');

        // Pastikan direktori penyimpanan ada
        Storage::disk('public')->makeDirectory('categories');
        Storage::disk('public')->makeDirectory('products');

        // --- Seed Kategori ---
        $categories = [
            [
                'name' => 'Kue Kering',
                'placeholder_url' => 'https://placehold.co/600x400/E9D8A6/941B0C?text=Kue+Kering', // Contoh URL dengan warna
            ],
            [
                'name' => 'Kue Basah',
                'placeholder_url' => 'https://placehold.co/600x400/A8DADC/1D3557?text=Kue+Basah', // Contoh URL dengan warna
            ],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryName = $category['name'];
            $placeholderUrl = $category['placeholder_url'];
            $categorySlug = Str::slug($categoryName);
            $imagePath = 'categories/' . $categorySlug . '.png'; // Path penyimpanan lokal

            try {
                $response = Http::get($placeholderUrl);
                if ($response->successful()) {
                    Storage::disk('public')->put($imagePath, $response->body());
                    $this->command->info("Berhasil mengunduh gambar kategori: {$categoryName}");
                } else {
                    $this->command->warn("Gagal mengunduh gambar kategori {$categoryName} dari {$placeholderUrl}. Status: " . $response->status());
                    $imagePath = 'categories/default.png'; // Gunakan gambar default jika gagal
                    // Anda mungkin perlu membuat file default.png di storage/app/public/categories
                }
            } catch (\Exception $e) {
                $this->command->error("Error saat mengunduh gambar kategori {$categoryName}: " . $e->getMessage());
                $imagePath = 'categories/default.png'; // Gunakan gambar default jika error
            }


            $categoryId = DB::table('categories')->insertGetId([
                'category_name' => $categoryName,
                'image' => $imagePath, // Simpan path lokal
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $categoryIds[$categoryName] = $categoryId; // Simpan ID untuk digunakan nanti
        }


        // --- Nama Produk ---
        $kueKeringNames = [
            'Nastar', 'Kastengel', 'Putri Salju', 'Lidah Kucing', 'Sagu Keju',
            'Semprit', 'Kue Kacang', 'Cornflakes Cookies', 'Chocolate Chip Cookies', 'Palm Cheese Cookies',
        ];

        $kueBasahNames = [
            'Onde-Onde', 'Klepon', 'Serabi', 'Dadar Gulung', 'Bugis Mandi',
            'Nagasari', 'Lumpia Basah', 'Kue Talam', 'Bikang Ambon', 'Wajik',
        ];


        // --- Seed Kue Kering ---
        $this->seedProducts(
            $kueKeringNames,
            $categoryIds['Kue Kering'],
            $faker,
            [25000, 50000], // original price range
            [20000, 45000], // sale price range
            [50, 200],      // stock range
            [250, 500]       // weight range
        );


        // --- Seed Kue Basah ---
        $this->seedProducts(
            $kueBasahNames,
            $categoryIds['Kue Basah'],
            $faker,
            [10000, 30000], // original price range
            [8000, 25000],  // sale price range
            [30, 100],      // stock range
            [100, 300]       // weight range
        );

        // --- Buat Storage Link ---
        $this->createStorageLink();

        $this->command->info('Data kategori, produk, dan gambar produk (tersimpan lokal) berhasil di-seed!');
    }

    /**
     * Helper function to seed products and their images.
     */
    private function seedProducts(array $productNames, int $categoryId, \Faker\Generator $faker, array $originalPriceRange, array $salePriceRange, array $stockRange, array $weightRange): void
    {
        foreach ($productNames as $name) {
            $slug = Str::slug($name);
            $productId = DB::table('products')->insertGetId([
                'category_id' => $categoryId,
                'product_name' => $name,
                'original_price' => $faker->numberBetween($originalPriceRange[0], $originalPriceRange[1]),
                'sale_price' => $faker->optional(0.3)->numberBetween($salePriceRange[0], $salePriceRange[1]), // 30% chance of having sale price
                'stock' => $faker->numberBetween($stockRange[0], $stockRange[1]),
                'weight' => $faker->numberBetween($weightRange[0], $weightRange[1]),
                'description' => $faker->paragraph(2),
                'slug' => $slug, // Gunakan slug yang sudah dibuat
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Seed Gambar Produk
            for ($i = 0; $i < 1; $i++) {
                $placeholderUrl = 'https://placehold.co/600x400?text=' . urlencode($name) . '+' . ($i + 1);
                $imagePath = 'products/' . $slug . '-' . ($i + 1) . '.png'; // Path penyimpanan lokal

                try {
                    $response = Http::get($placeholderUrl);
                    if ($response->successful()) {
                        Storage::disk('public')->put($imagePath, $response->body());
                        $this->command->comment(" -> Mengunduh gambar produk: {$name} (" . ($i + 1) . ")"); // Gunakan comment untuk detail
                    } else {
                        $this->command->warn(" -> Gagal mengunduh gambar produk {$name} (" . ($i + 1) . ") dari {$placeholderUrl}. Status: " . $response->status());
                        $imagePath = 'products/default.png'; // Gunakan gambar default jika gagal
                        // Anda mungkin perlu membuat file default.png di storage/app/public/products
                    }
                } catch (\Exception $e) {
                    $this->command->error(" -> Error saat mengunduh gambar produk {$name} (" . ($i + 1) . "): " . $e->getMessage());
                    $imagePath = 'products/default.png'; // Gunakan gambar default jika error
                }


                DB::table('product_images')->insert([
                    'product_id' => $productId,
                    'image' => $imagePath, // Simpan path lokal
                    'is_primary' => ($i === 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Create the storage link if it doesn't exist.
     */
    private function createStorageLink(): void
    {
        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        if (!file_exists($linkPath)) {
            try {
                // Opsi 1: Menggunakan Artisan call (memerlukan use Illuminate\Support\Facades\Artisan;)
                Artisan::call('storage:link');
                $this->command->info('Symbolic link [public/storage] created.');

                // Opsi 2: Menggunakan symlink() bawaan PHP (lebih direct)
                // symlink($targetPath, $linkPath);
                // $this->command->info('Symbolic link [public/storage] created.');

            } catch (\Exception $e) {
                $this->command->error('Could not create symbolic link: ' . $e->getMessage());
                $this->command->warn('Please run "php artisan storage:link" manually.');
            }
        } else {
             $this->command->info('Symbolic link [public/storage] already exists.');
        }
    }
}