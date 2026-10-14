<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\CategoriesDataTable;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(CategoriesDataTable $dataTable)
    {
        return $dataTable->render('admin.categories.index');
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();

        Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria cadastrada com sucesso!');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category->id);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Category $category)
    {
        // Impede exclusão se houver produtos vinculados
        if ($category->products()->count() > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Não é possível excluir: existem produtos vinculados a esta categoria.');
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categoria excluída com sucesso!');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'icon'        => ['nullable', 'string', 'max:60'],
            'position'    => ['nullable', 'integer', 'min:0'],
            'active'      => ['nullable', 'boolean'],
            'image'       => ['nullable', 'image', 'max:2048'],
        ]);
    }
}