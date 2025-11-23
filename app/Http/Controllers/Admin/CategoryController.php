<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // Import Str

class CategoryController extends Controller
{
    /**
     * [DIHAPUS] Halaman index tidak dipakai lagi
     */
    // public function index() { ... }

    /**
     * [DIHAPUS] Fungsi store halaman manajemen tidak dipakai lagi
     */
    // public function store(Request $request) { ... }

    /**
     * Mengambil semua kategori sebagai JSON
     * (untuk di-load oleh Alpine.js)
     */
    public function indexJson()
    {
        $categories = Category::orderBy('name')->get();
        return response()->json($categories);
    }

    /**
     * Menyimpan kategori baru via AJAX
     * (dari modal pop-up)
     */
    public function storeAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json($category, 201); // 201 = Created
    }

    /**
     * [BARU] Update kategori via AJAX (untuk perbaiki typo)
     */
    public function updateAjax(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json($category);
    }

    /**
     * [BARU] Menghapus kategori via AJAX
     */
    public function destroyAjax(Category $category)
    {
        try {
            // [LOGIKA KEAMANAN] Cek jika ada produk yang masih menggunakan kategori ini
            if ($category->products()->count() > 0) {
                return response()->json(['message' => 'Gagal menghapus. Kategori ini masih digunakan oleh produk.'], 422); // 422 Unprocessable Entity
            }

            $category->delete();
            return response()->json(['message' => 'Kategori berhasil dihapus.'], 200); // 200 OK

        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus kategori.'], 500);
        }
    }
}