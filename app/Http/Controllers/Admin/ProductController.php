<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $productsQuery = Product::with(['category', 'baseUnit.unit'])->latest(); // Terbaru di atas
        
        if ($search = $request->input('search')) {
            $productsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('kode_produk', 'like', "%{$search}%");
            });
        }
        $products = $productsQuery->paginate(10)->withQueryString();
        
        return view('admin.products.index', compact('products'));
    }

    public function create() { return redirect()->route('admin.products.index'); }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kode_produk' => 'required|string|max:100|unique:products,kode_produk',
            'foto_produk' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
            'category_id' => 'required|exists:categories,id', // Wajib
            'status' => 'required|in:active,inactive',
            'units' => 'required|array|min:1',
            'units.*.unit_id' => 'required|exists:units,id',
            'units.*.price' => 'required|numeric|min:0', 
            'units.*.conversion' => 'required|integer|min:1',
            'is_base_unit_index' => 'required|integer|min:0', 
        ], [
            'category_id.required' => 'Kategori wajib dipilih!',
            'units.required' => 'Minimal harus ada 1 satuan produk.',
        ]);

        DB::beginTransaction();
        try {
            $fotoPath = $request->hasFile('foto_produk') ? $request->file('foto_produk')->store('products', 'public') : null;

            $product = Product::create([
                'name' => $request->name,
                'kode_produk' => $request->kode_produk,
                'foto_produk' => $fotoPath, 
                'category_id' => $request->category_id,
                'description' => $request->description,
                'status' => $request->status,
                'stock_in_base_unit' => 0, 
            ]);

            foreach ($request->units as $index => $unitData) {
                $product->units()->create([
                    'unit_id' => $unitData['unit_id'],
                    'price' => $unitData['price'], 
                    'conversion_to_base' => ((int)$request->is_base_unit_index === $index) ? 1 : $unitData['conversion'],
                    'is_base_unit' => ((int)$request->is_base_unit_index === $index),
                    'harga_beli_modal' => 0,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function editJson(Product $product)
    {
        $product->load('units.unit');
        return response()->json($product);
    }

    // [PERBAIKAN UTAMA DI SINI]
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'kode_produk' => 'required|string|max:100|unique:products,kode_produk,' . $product->id,
            'foto_produk' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:active,inactive',
            'units' => 'required|array|min:1',
            'units.*.unit_id' => 'required|exists:units,id',
            'units.*.price' => 'required|numeric|min:0',
            'units.*.conversion' => 'required|integer|min:1',
            'is_base_unit_index' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 1. Update Data Utama (Nama, Kode, Kategori, dll)
            // Ini aman dilakukan walau ada riwayat stok. Nama di riwayat otomatis berubah.
            $dataToUpdate = [
                'name' => $request->name,
                'kode_produk' => $request->kode_produk,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'status' => $request->status,
            ];

            if ($request->hasFile('foto_produk')) {
                if ($product->foto_produk) Storage::disk('public')->delete($product->foto_produk);
                $dataToUpdate['foto_produk'] = $request->file('foto_produk')->store('products', 'public');
            }
            
            $product->update($dataToUpdate);

            // 2. Update Satuan (Metode Cerdas: Update Existing, Create New)
            // Kita simpan ID satuan yang diproses agar sisanya bisa kita cek untuk dihapus
            $processedUnitIds = [];

            foreach ($request->units as $index => $unitData) {
                $isBase = ((int)$request->is_base_unit_index === $index);
                $conversion = $isBase ? 1 : $unitData['conversion'];

                // Cari apakah produk ini sudah punya satuan jenis ini (misal: ID 'Dos')
                $existingUnit = $product->units()->where('unit_id', $unitData['unit_id'])->first();

                if ($existingUnit) {
                    // Jika ada, UPDATE datanya saja. ID-nya tetap, jadi riwayat AMAN.
                    $existingUnit->update([
                        'price' => $unitData['price'],
                        'conversion_to_base' => $conversion,
                        'is_base_unit' => $isBase,
                    ]);
                    $processedUnitIds[] = $existingUnit->id;
                } else {
                    // Jika belum ada, BUAT BARU.
                    $newUnit = $product->units()->create([
                        'unit_id' => $unitData['unit_id'],
                        'price' => $unitData['price'],
                        'conversion_to_base' => $conversion,
                        'is_base_unit' => $isBase,
                        'harga_beli_modal' => 0, // Default 0
                    ]);
                    $processedUnitIds[] = $newUnit->id;
                }
            }

            // 3. Hapus Satuan yang Dibuang User (Safety Check)
            // Ambil satuan di DB yang TIDAK ada di form inputan user
            $unitsToDelete = $product->units()->whereNotIn('id', $processedUnitIds)->get();

            foreach ($unitsToDelete as $delUnit) {
                try {
                    // Coba hapus. Kalau ada riwayat stok, database akan menolak (Constraint).
                    $delUnit->delete();
                } catch (\Exception $e) {
                    // Jika gagal hapus karena dipakai, batalkan semua dan beri pesan jelas
                    DB::rollBack();
                    return back()->withInput()->with('error', 'GAGAL UPDATE: Anda mencoba menghapus satuan "' . ($delUnit->unit->name ?? 'Unknown') . '" yang sudah memiliki riwayat stok/penjualan. Silakan biarkan satuan tersebut, atau nonaktifkan produk jika tidak ingin dipakai.');
                }
            }

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        try {
            // Cek Riwayat Stok
            $inStock = DB::table('stock_entry_details')->whereIn('product_unit_id', $product->units->pluck('id'))->exists();
            
            if ($inStock) {
                return redirect()->route('admin.products.index')->with('error', 'GAGAL HAPUS: Produk ini memiliki riwayat stok. Data tidak boleh dihapus agar laporan valid. Silakan ubah status menjadi NONAKTIF.');
            }

            if ($product->foto_produk) Storage::disk('public')->delete($product->foto_produk);
            $product->delete();
            return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.products.index')->with('error', 'Terjadi kesalahan saat menghapus produk.');
        }
    }

    public function toggleStatus(Product $product)
    {
        $newStatus = $product->status === 'active' ? 'inactive' : 'active';
        $product->update(['status' => $newStatus]);
        return response()->json(['status' => 'success']);
    }
}