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

    private array $bookImagePool = [
        'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1491841550275-ad7854e35ca6?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1519682337058-a94d519337bc?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1524578271613-d550eacf6090?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1455885666463-9bdf805818c0?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1541963463532-d68292c34b19?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1474932430478-367dbb6832c1?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=80',
    ];

    
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

        // Isi beberapa buku utama yang tampil konsisten di landing page.
        $featuredBooks = [
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'price' => 98000,
                'image_url' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=900&q=80',
                'image_urls' => [
                    'https://images.unsplash.com/photo-1455885666463-9bdf805818c0?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=900&q=80',
                ],
                'category_slug' => 'pengembangan-diri',
            ],
            [
                'title' => 'The Psychology of Money',
                'author' => 'Morgan Housel',
                'price' => 110000,
                'image_url' => 'https://images.unsplash.com/photo-1491841550275-ad7854e35ca6?auto=format&fit=crop&w=900&q=80',
                'image_urls' => [
                    'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=900&q=80',
                ],
                'category_slug' => 'bisnis',
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'price' => 145000,
                'image_url' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=900&q=80',
                'image_urls' => [
                    'https://images.unsplash.com/photo-1541963463532-d68292c34b19?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1474932430478-367dbb6832c1?auto=format&fit=crop&w=900&q=80',
                ],
                'category_slug' => 'teknologi',
            ],
            [
                'title' => 'The Midnight Library',
                'author' => 'Matt Haig',
                'price' => 92000,
                'image_url' => 'https://images.unsplash.com/photo-1519682337058-a94d519337bc?auto=format&fit=crop&w=900&q=80',
                'image_urls' => [
                    'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1524578271613-d550eacf6090?auto=format&fit=crop&w=900&q=80',
                ],
                'category_slug' => 'fiksi',
            ],
        ];

        $categoryProfiles = [
            'fiksi' => [
                'authors' => ['Rizka Anindya', 'Mahesa Pradana', 'Lana Prameswari', 'Ardan Saputra'],
                'patterns' => ['%s di Ujung %s', 'Rahasia %s dan %s', 'Catatan %s dari %s'],
                'word_a' => ['Senja', 'Hujan', 'Langit', 'Cahaya', 'Rindu', 'Malam'],
                'word_b' => ['Kota', 'Pelabuhan', 'Lorong', 'Danau', 'Stasiun', 'Hutan'],
                'price' => [78000, 145000],
            ],
            'teknologi' => [
                'authors' => ['Naufal Wijaya', 'Dhea Kurnia', 'Faris Pramudito', 'Citra Mahardika'],
                'patterns' => ['Praktik %s untuk %s', 'Membangun %s dengan %s', 'Blueprint %s Modern'],
                'word_a' => ['API', 'Sistem', 'Frontend', 'Backend', 'Arsitektur', 'Automasi'],
                'word_b' => ['Laravel', 'React', 'Microservices', 'Cloud', 'Testing', 'CI/CD'],
                'price' => [98000, 220000],
            ],
            'bisnis' => [
                'authors' => ['Gilang Prakoso', 'Nadine Suryani', 'Bimo Ardian', 'Salma Hapsari'],
                'patterns' => ['Strategi %s untuk %s', '%s dan Seni %s', 'Playbook %s Masa Kini'],
                'word_a' => ['Branding', 'Penjualan', 'Negosiasi', 'Manajemen', 'Investasi', 'Leadership'],
                'word_b' => ['UMKM', 'Skalabilitas', 'Cashflow', 'Tim Kecil', 'Growth', 'Ekspansi'],
                'price' => [85000, 198000],
            ],
            'pengembangan-diri' => [
                'authors' => ['Nabila Rahmat', 'Yoga Firmansyah', 'Aurel Paramita', 'Danu Prasetyo'],
                'patterns' => ['Langkah %s untuk %s', '%s dan Kekuatan %s', 'Jurnal %s Harian'],
                'word_a' => ['Fokus', 'Disiplin', 'Kebiasaan', 'Mindset', 'Produktif', 'Percaya Diri'],
                'word_b' => ['Konsistensi', 'Pagi Hari', 'Tujuan Hidup', 'Perubahan Kecil', 'Pemulihan Diri', 'Kemajuan'],
                'price' => [70000, 165000],
            ],
        ];

        $categorySlugs = $categories->pluck('slug')->all();

        $dummyBooks = [];
        for ($i = 1; $i <= 46; $i++) {
            $categorySlug = $categorySlugs[($i - 1) % count($categorySlugs)];
            $profile = $categoryProfiles[$categorySlug] ?? $categoryProfiles['fiksi'];

            $pattern = $profile['patterns'][($i - 1) % count($profile['patterns'])];
            $wordA = $profile['word_a'][($i - 1) % count($profile['word_a'])];
            $wordB = $profile['word_b'][($i + 1) % count($profile['word_b'])];

            if (substr_count($pattern, '%s') >= 2) {
                $title = sprintf($pattern, $wordA, $wordB);
            } else {
                $title = sprintf($pattern, $wordA);
            }

            $title .= ' #' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            $minPrice = $profile['price'][0];
            $maxPrice = $profile['price'][1];

            $dummyBooks[] = [
                'title' => $title,
                'author' => $profile['authors'][($i - 1) % count($profile['authors'])],
                'price' => random_int($minPrice, $maxPrice),
                'category_slug' => $categorySlug,
            ];
        }

        $seedBooks = array_merge($featuredBooks, $dummyBooks);

        foreach ($seedBooks as $index => $book) {
            $category = $categories->firstWhere('slug', $book['category_slug']);

            if (! $category) {
                continue;
            }

            $images = $this->buildBookImages($book['image_url'] ?? null, $book['image_urls'] ?? null);
            $narrative = $this->buildBookNarrative(
                $book['title'],
                $book['author'],
                $book['category_slug']
            );
            $isbn = $book['isbn'] ?? $this->generateIsbn13FromNumber($index + 1);

            Book::updateOrCreate(
                ['title' => $book['title']],
                [
                    'author' => $book['author'],
                    'isbn' => $isbn,
                    'price' => $book['price'],
                    'image_url' => $images['image_url'],
                    'image_urls' => $images['image_urls'],
                    'description' => $narrative['description'],
                    'detail' => $narrative['detail'],
                    'category_id' => $category->id,
                ]
            );
        }

        $this->call(OrderDemoSeeder::class);
    }

    private function buildBookNarrative(string $title, string $author, string $categorySlug): array
    {
        $templates = [
            'fiksi' => [
                'description' => sprintf('Novel "%s" karya %s menghadirkan alur emosional dengan konflik yang dekat dengan kehidupan sehari-hari.', $title, $author),
                'detail' => sprintf('Buku ini cocok untuk pembaca yang menyukai karakter kuat, perkembangan cerita bertahap, dan suasana naratif yang hangat. "%s" menyajikan perjalanan tokoh dengan latar yang imajinatif namun tetap relevan.', $title),
            ],
            'teknologi' => [
                'description' => sprintf('"%s" oleh %s membahas konsep teknologi modern secara praktis, dari dasar hingga implementasi nyata.', $title, $author),
                'detail' => sprintf('Materi dalam buku ini dirancang untuk developer dan pembelajar mandiri. Setiap bab fokus pada penerapan langsung, best practice, serta pola kerja yang bisa dipakai di proyek produksi.'),
            ],
            'bisnis' => [
                'description' => sprintf('"%s" ditulis %s sebagai panduan strategi bisnis dengan pendekatan yang aplikatif dan mudah dipraktikkan.', $title, $author),
                'detail' => sprintf('Isi buku menekankan pengambilan keputusan, pengelolaan tim, dan penguatan model bisnis. Cocok untuk pemilik usaha, manajer, maupun profesional yang ingin meningkatkan performa bisnis.'),
            ],
            'pengembangan-diri' => [
                'description' => sprintf('"%s" karya %s berfokus pada pengembangan kebiasaan, disiplin diri, dan peningkatan kualitas hidup secara bertahap.', $title, $author),
                'detail' => sprintf('Buku ini berisi langkah-langkah sederhana yang dapat diterapkan harian. Pendekatannya ringan, reflektif, dan dirancang untuk membantu pembaca membangun progres jangka panjang.'),
            ],
        ];

        return $templates[$categorySlug] ?? [
            'description' => sprintf('"%s" oleh %s adalah buku dummy untuk kebutuhan pengujian katalog.', $title, $author),
            'detail' => 'Konten detail ini disiapkan untuk memastikan halaman produk tampil konsisten saat proses development.',
        ];
    }

    private function buildBookImages(?string $primaryImage, ?array $additionalImages = null): array
    {
        $images = $this->bookImagePool;
        shuffle($images);

        $main = is_string($primaryImage) && trim($primaryImage) !== ''
            ? trim($primaryImage)
            : $images[0];

        $extras = [];
        if (is_array($additionalImages)) {
            foreach ($additionalImages as $url) {
                if (is_string($url) && trim($url) !== '' && trim($url) !== $main) {
                    $extras[] = trim($url);
                }
            }
        }

        foreach ($images as $url) {
            if ($url === $main || in_array($url, $extras, true)) {
                continue;
            }

            $extras[] = $url;

            if (count($extras) >= 2) {
                break;
            }
        }

        return [
            'image_url' => $main,
            'image_urls' => array_values(array_unique($extras)),
        ];
    }

    private function generateIsbn13FromNumber(int $number): string
    {
        // Prefix 978 + 9 digit body + 1 digit checksum untuk ISBN-13 valid.
        $body = '978' . str_pad((string) $number, 9, '0', STR_PAD_LEFT);

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $body[$i];
            $sum += ($i % 2 === 0) ? $digit : ($digit * 3);
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $body . (string) $checkDigit;
    }
}
