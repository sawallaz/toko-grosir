<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'baseUnit.unit', 'units'])
            ->where('status', 'active')
            ->whereHas('baseUnit'); // Hanya produk yg punya satuan

        // Filter Kategori
        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter Cari
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Sorting
        if ($request->sort == 'price_low') {
            $query->join('product_units', function($join) {
                $join->on('products.id', '=', 'product_units.product_id')
                     ->where('product_units.is_base_unit', 1);
            })->orderBy('product_units.price', 'asc')
              ->select('products.*');
        } elseif ($request->sort == 'price_high') {
            $query->join('product_units', function($join) {
                $join->on('products.id', '=', 'product_units.product_id')
                     ->where('product_units.is_base_unit', 1);
            })->orderBy('product_units.price', 'desc')
              ->select('products.*');
        } elseif ($request->sort == 'stock') {
            $query->orderBy('stock_in_base_unit', 'DESC');
        } else {
            $query->latest();
        }

        $products = $query->paginate(20);
        $categories = Category::has('products')->orderBy('name')->get();

        return view('customer.home', compact('products', 'categories'));
    }

    public function show($id)
    {
        $product = Product::with(['category', 'units.unit', 'units.wholesalePrices'])
            ->where('status', 'active')
            ->findOrFail($id);
            
        return view('customer.product.show', compact('product'));
    }
}