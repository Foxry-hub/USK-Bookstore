<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    // Validasi name biar konsisten di store dan update, jadi nggak perlu repeat rules.
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategoryName($request);

        $this->createCategoryWithSlug($validated['name']);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validateCategoryName($request, $category->id);

        $category->update($this->prepareCategoryUpdateData($validated['name']));

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    private function validateCategoryName(Request $request, ?int $exceptId = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
        ];

        // Pas update, exclude slug category yang lagi diedit dari unique check.
        if ($exceptId !== null) {
            $rules['name'][4] = "unique:categories,name,{$exceptId}";
        }

        return $request->validate($rules);
    }

    private function prepareCategoryUpdateData(string $name): array
    {
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }

    private function createCategoryWithSlug(string $name): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }
}
