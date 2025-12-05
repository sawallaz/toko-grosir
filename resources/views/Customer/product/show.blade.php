@extends('layouts.customer')

@section('content')
@php
    // Persiapkan data Unit & Grosir di Server Side agar JS tidak bingung
    $unitsData = $product->units->map(function($u) {
        return [
            'id' => $u->id,
            'name' => $u->unit->name,
            'price' => $u->price,
            'base' => $u->is_base_unit,
            'wholesale' => $u->wholesalePrices->map(function($w) {
                return [
                    'min' => $w->min_qty,
                    'price' => $w->price
                ];
            })
        ];
    })->values(); // Reset keys
    
    $initialUnit = $product->baseUnit ?? $product->units->first();
@endphp

<div class="bg-white min-h-screen pb-24" x-data="productDetail(@json($unitsData), {{ $initialUnit->id ?? 'null' }})">
    
    <!-- Gambar Produk (Full Width di Mobile) -->
    <div class="relative w-full h-96 bg-gray-100 overflow-hidden group">
        @if($product->foto_produk)
            <img src="{{ Storage::url($product->foto_produk) }}" class="w-full h-full object-cover transition-transform duration-700 hover:scale-110">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-gray-300">
                <svg class="w-20 h-20 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.001M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="text-sm font-medium">Tidak ada gambar</span>
            </div>
        @endif
        
        <!-- Tombol Back -->
        <a href="{{ route('home') }}" class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm p-2.5 rounded-full text-gray-800 shadow-lg hover:bg-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
    </div>

    <!-- Informasi Produk -->
    <div class="relative -mt-10 bg-white rounded-t-[2.5rem] px-6 pt-8 pb-6 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] z-10">
        
        <!-- Kategori & Stok -->
        <div class="flex items-center justify-between mb-3">
            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-xs font-bold uppercase tracking-wider rounded-full">
                {{ $product->category->name ?? 'Umum' }}
            </span>
            <div class="flex items-center gap-1 text-xs font-bold" :class="stock > 0 ? 'text-green-600' : 'text-red-500'">
                <span class="w-2 h-2 rounded-full" :class="stock > 0 ? 'bg-green-500' : 'bg-red-500'"></span>
                Stok: {{ $product->stock_in_base_unit }}
            </div>
        </div>

        <!-- Judul -->
        <h1 class="text-2xl md:text-3xl font-black text-gray-900 leading-tight mb-1">{{ $product->name }}</h1>
        <p class="text-gray-400 text-sm font-mono mb-6">Kode: {{ $product->kode_produk }}</p>

        <!-- Pilihan Satuan -->
        <div class="mb-6">
            <label class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-3">Pilih Kemasan</label>
            <div class="flex flex-wrap gap-3">
                <template x-for="u in units" :key="u.id">
                    <button @click="selectUnit(u)" 
                            :class="selectedUnit.id === u.id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 transform scale-105' : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-300 hover:bg-indigo-50'"
                            class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200 flex-shrink-0 border">
                        <span x-text="u.name"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Info Harga Grosir (Alert) -->
        <div x-show="selectedUnit.wholesale && selectedUnit.wholesale.length > 0" x-transition class="mb-6 p-4 bg-green-50 border border-green-100 rounded-2xl">
            <div class="flex items-center gap-2 mb-2">
                <div class="bg-green-500 text-white p-1 rounded-full"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                <span class="text-xs font-bold text-green-700 uppercase">Harga Grosir Tersedia</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <template x-for="ws in selectedUnit.wholesale">
                    <div class="bg-white px-3 py-1.5 rounded-lg text-xs border border-green-200 shadow-sm">
                        Beli <strong class="text-green-700" x-text="ws.min"></strong> = <strong class="text-green-700" x-text="formatRupiah(ws.price)"></strong>
                    </div>
                </template>
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="border-t border-gray-100 pt-6">
            <h3 class="font-bold text-gray-900 mb-3 text-lg">Deskripsi Produk</h3>
            <div class="text-gray-500 text-sm leading-relaxed space-y-2">
                <p>{{ $product->description ?: 'Tidak ada deskripsi untuk produk ini.' }}</p>
            </div>
        </div>
    </div>

    <!-- STICKY FOOTER BAR -->
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-100 p-4 pb-6 z-50 shadow-[0_-10px_30px_rgba(0,0,0,0.05)]">
        <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center gap-4">
            
            <!-- Harga Total Live -->
            <div class="flex-1 w-full flex justify-between md:block items-center">
                <div class="text-xs text-gray-400 font-bold uppercase">Total Harga</div>
                <div class="flex items-baseline gap-2">
                    <div class="text-3xl font-black text-indigo-700" x-text="formatRupiah(currentPrice * qty)"></div>
                    <span x-show="isWholesaleActive" class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold">Grosir Aktif</span>
                </div>
            </div>

            <!-- Form Cart -->
            <form action="{{ route('cart.add') }}" method="POST" class="w-full md:w-auto flex gap-3">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="unit_id" :value="selectedUnit.id">
                
                <!-- Qty Control -->
                <div class="flex items-center bg-gray-100 rounded-2xl p-1 h-14">
                    <button type="button" @click="decrement()" class="w-12 h-full flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-white hover:shadow-sm rounded-xl transition text-xl font-bold">-</button>
                    <input type="number" name="quantity" x-model="qty" class="w-12 bg-transparent border-none text-center font-black text-lg p-0 focus:ring-0 text-gray-800" readonly>
                    <button type="button" @click="increment()" class="w-12 h-full flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-white hover:shadow-sm rounded-xl transition text-xl font-bold">+</button>
                </div>

                <!-- Button -->
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-200 transform active:scale-95 transition flex items-center justify-center gap-2 min-w-[180px]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    + KERANJANG
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productDetail', (unitsData, initialId) => ({
            units: unitsData,
            selectedUnit: {},
            qty: 1,
            currentPrice: 0,
            isWholesaleActive: false,
            stock: {{ $product->stock_in_base_unit }},

            init() {
                // Pilih unit awal
                this.selectedUnit = this.units.find(u => u.id == initialId) || this.units[0];
                this.calculatePrice();
                this.$watch('qty', () => this.calculatePrice());
            },

            selectUnit(unit) {
                this.selectedUnit = unit;
                this.calculatePrice();
            },

            increment() { this.qty++; },
            decrement() { if(this.qty > 1) this.qty--; },

            calculatePrice() {
                let basePrice = Number(this.selectedUnit.price);
                this.isWholesaleActive = false;
                this.currentPrice = basePrice;

                // Logika Grosir
                if (this.selectedUnit.wholesale && this.selectedUnit.wholesale.length > 0) {
                    let sortedRules = this.selectedUnit.wholesale.sort((a, b) => b.min - a.min);
                    let appliedRule = sortedRules.find(rule => this.qty >= rule.min);
                    
                    if (appliedRule) {
                        this.currentPrice = Number(appliedRule.price);
                        this.isWholesaleActive = true;
                    }
                }
            },

            formatRupiah(n) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n);
            }
        }));
    });
</script>
@endsection