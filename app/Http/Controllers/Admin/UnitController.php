<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
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
     * Mengambil semua satuan sebagai JSON
     */
    public function indexJson()
    {
        $units = Unit::orderBy('name')->get();
        return response()->json($units);
    }

    /**
     * Menyimpan satuan baru via AJAX
     */
    public function storeAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'short_name' => 'required|string|max:10|unique:units,short_name',
        ]);

        $unit = Unit::create([
            'name' => $request->name,
            'short_name' => $request->short_name,
        ]);

        return response()->json($unit, 201);
    }

    /**
     * [BARU] Update satuan via AJAX (untuk perbaiki typo)
     */
    public function updateAjax(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'short_name' => 'required|string|max:10|unique:units,short_name,' . $unit->id,
        ]);

        $unit->update([
            'name' => $request->name,
            'short_name' => $request->short_name,
        ]);

        return response()->json($unit);
    }

    /**
     * [BARU] Menghapus satuan via AJAX
     */
    public function destroyAjax(Unit $unit)
    {
        try {
            // [LOGIKA KEAMANAN] Cek jika ada produk yang masih menggunakan satuan ini
            if ($unit->productUnits()->count() > 0) {
                return response()->json(['message' => 'Gagal menghapus. Satuan ini masih digunakan oleh produk.'], 422);
            }

            $unit->delete();
            return response()->json(['message' => 'Satuan berhasil dihapus.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus satuan.'], 500);
        }
    }
}