<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_category_that_still_has_books(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $category = $this->createCategory('Fiksi');

        Book::create([
            'category_id' => $category->id,
            'title' => 'Novel Uji',
            'isbn' => '9780000000001',
            'author' => 'Penulis Uji',
            'price' => 50000,
            'stock' => 10,
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category));

        $response
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_can_delete_category_that_has_no_books(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $category = $this->createCategory('Non Fiksi');

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category));

        $response
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    private function createCategory(string $name): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(6),
        ]);
    }
}
