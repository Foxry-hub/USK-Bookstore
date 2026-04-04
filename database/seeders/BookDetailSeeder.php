<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookDetailSeeder extends Seeder
{
    public function run(): void
    {
        $details = [
            1 => [
                'description' => 'Buku self-help yang mengubah hidup dengan prinsip-prinsip sederhana namun kuat untuk mencapai kebiasaan baik.',
                'detail' => 'Atomic Habits adalah buku terlaris tentang bagaimana membangun kebiasaan baik dan menghilangkan kebiasaan buruk. Penulis James Clear memberikan panduan praktis tentang:
• Sistem 1% - Perbaikan kecil setiap hari menghasilkan transformasi besar
• Psychological Triggers - Bagaimana otak kita mengaktifkan kebiasaan
• Implementation Intentions - Strategi konkret untuk memulai perubahan
• Habit Stacking - Menggabungkan kebiasaan baru dengan kebiasaan lama
• Environmental Design - Menciptakan lingkungan yang mendukung tujuan

Buku ini dilengkapi dengan contoh-contoh nyata, case studies, dan framework praktis yang bisa langsung diterapkan. Cocok untuk siapa saja yang ingin meningkatkan produktivitas, kesehatan, atau fokus.'
            ],
            2 => [
                'description' => 'Panduan lengkap tentang psikologi keputusan dan bagaimana pikiran kita membuat pilihan.',
                'detail' => 'Thinking, Fast and Slow karya Daniel Kahneman mengungkap misteri di balik proses berpikir manusia. Buku ini menjelaskan dua sistem pemikiran:

• Sistem 1: Berpikir cepat, otomatis, dan intuitif
• Sistem 2: Berpikir lambat, terukur, dan rasional

Kahneman, pemenang Nobel di bidang ekonomi, membawa penelitian puluhan tahun tentang kognitif bias, heuristics, dan keputusan yang tidak rasional. Buku ini akan membuka wawasan baru tentang mengapa kita membuat keputusan tertentu.'
            ],
            3 => [
                'description' => 'Kisah fiksi petualangan yang memikat tentang perjalanan spiritual dan pencarian makna hidup.',
                'detail' => 'The Alchemist adalah novel filosofis karya Paulo Coelho yang telah menginspirasi jutaan pembaca di seluruh dunia. Cerita tentang Santiago, seorang gembala muda yang melakukan perjalanan panjang mencari hartanya.

Dalam perjalanannya, Santiago belajar banyak hal tentang:
• Personal legend - Takdir dan tujuan hidup setiap manusia
• Omen - Tanda-tanda dari alam semesta
• The language of the world - Bahasa universal yang menghubungkan semua makhluk

Buku yang indah dengan pesan universal tentang keberanian, kepercayaan, dan mengikuti impian. Sempurna untuk dibaca berulang kali.'
            ],
        ];

        foreach ($details as $bookId => $data) {
            Book::where('id', $bookId)->update($data);
        }
    }
}
