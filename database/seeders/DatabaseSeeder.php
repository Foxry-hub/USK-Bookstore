<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@bookstore.test');
        $adminPassword = env('ADMIN_PASSWORD');

        if (! $adminPassword) {
            throw new RuntimeException('Isi ADMIN_PASSWORD di file .env dulu sebelum menjalankan seeder.');
        }

        // Bikin akun admin default biar langsung bisa akses dashboard.
        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin BookStore',
                'password' => Hash::make($adminPassword),
                'is_admin' => true,
            ]
        );

        // Bikin user contoh untuk simulasi pembeli.
        User::factory(5)->create();

        // Seed beberapa kategori biar katalog tidak kosong.
        $categories = collect([
            ['name' => 'Fiksi', 'slug' => 'fiksi'],
            ['name' => 'Teknologi', 'slug' => 'teknologi'],
            ['name' => 'Bisnis', 'slug' => 'bisnis'],
            ['name' => 'Pengembangan Diri', 'slug' => 'pengembangan-diri'],
        ])->map(fn (array $item): Category => Category::updateOrCreate(['slug' => $item['slug']], $item));

        // Isi buku contoh lengkap dengan gambar bebas hak cipta dari Unsplash.
        $seedBooks = [
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'price' => 98000,
                'image_url' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=900&q=80',
                'category_slug' => 'pengembangan-diri',
            ],
            [
                'title' => 'The Psychology of Money',
                'author' => 'Morgan Housel',
                'price' => 110000,
                'image_url' => 'https://images.unsplash.com/photo-1491841550275-ad7854e35ca6?auto=format&fit=crop&w=900&q=80',
                'category_slug' => 'bisnis',
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'price' => 145000,
                'image_url' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=900&q=80',
                'category_slug' => 'teknologi',
            ],
            [
                'title' => 'The Midnight Library',
                'author' => 'Matt Haig',
                'price' => 92000,
                'image_url' => 'https://images.unsplash.com/photo-1519682337058-a94d519337bc?auto=format&fit=crop&w=900&q=80',
                'category_slug' => 'fiksi',
            ],
        ];

        foreach ($seedBooks as $book) {
            $category = $categories->firstWhere('slug', $book['category_slug']);

            if (! $category) {
                continue;
            }

            Book::updateOrCreate(
                ['title' => $book['title']],
                [
                    'author' => $book['author'],
                    'price' => $book['price'],
                    'image_url' => $book['image_url'],
                    'category_id' => $category->id,
                ]
            );
        }
    }
}
