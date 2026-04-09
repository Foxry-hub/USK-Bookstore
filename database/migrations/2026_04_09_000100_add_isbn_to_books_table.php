<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('isbn', 20)->nullable()->after('author');
        });

        DB::table('books')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($books): void {
                foreach ($books as $book) {
                    DB::table('books')
                        ->where('id', $book->id)
                        ->update([
                            'isbn' => $this->generateIsbn13FromNumber((int) $book->id),
                        ]);
                }
            });

        Schema::table('books', function (Blueprint $table) {
            $table->unique('isbn');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique(['isbn']);
            $table->dropColumn('isbn');
        });
    }

    private function generateIsbn13FromNumber(int $number): string
    {
        $body = '978' . str_pad((string) $number, 9, '0', STR_PAD_LEFT);

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $body[$i];
            $sum += ($i % 2 === 0) ? $digit : ($digit * 3);
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $body . (string) $checkDigit;
    }
};
