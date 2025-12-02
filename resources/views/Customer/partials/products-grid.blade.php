<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
    @forelse($products as $product)
    <div class="bg-white rounded-2xl shadow-soft border border-gray-100 overflow-hidden hover:shadow-hard hover:border-indigo-200 transition-all-300 group flex flex-col h-full">
        <!-- Product Image -->
        <div class="aspect-square w-full bg-gradient-to-br from-gray-50 to-gray-100 relative overflow-hidden">
            @if($product->foto_produk)
                <img 
                    src="{{ Storage::url($product->foto_produk) }}" 
                    alt="{{ $product->name }}"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    loading="lazy"
                >
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-300">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            @endif
            
            <!-- Stock Badge -->
            @if($product->stock_in_base_unit == 0)
                <div class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center">
                    <span class="bg-red-600 text-white text-xs font-bold px-3 py-2 rounded-full shadow-lg">STOK HABIS</span>
                </div>
            @elseif($product->stock_in_base_unit < 10)
                <div class="absolute top-3 left-3">
                    <span class="bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow">STOK TERBATAS</span>
                </div>
            @endif

            <!-- Quick Actions Overlay -->
            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-2">
                <button data-quick-view data-product-id="{{ $product->id }}" 
                       class="bg-white text-gray-900 px-4 py-2 rounded-xl font-bold hover:bg-gray-100 transition-all transform translate-y-4 group-hover:translate-y-0 duration-300 flex items-center gap-2">
                    👁️ Lihat Detail
                </button>
            </div>
        </div>

        <!-- Product Info -->
        <div class="p-4 flex-1 flex flex-col">
            <h4 class="font-bold text-gray-900 text-sm leading-snug mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                {{ $product->name }}
            </h4>
            
            <!-- Category -->
            <div class="mb-2">
                <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">
                    {{ $product->category->name ?? 'Umum' }}
                </span>
            </div>
            
            <div class="mt-auto space-y-3">
                <!-- Price -->
                <div class="flex items-baseline gap-1">
                    <span class="text-xs text-gray-500 font-medium">Rp</span>
                    <span class="text-lg font-black text-gray-900">
                        {{ number_format($product->baseUnit->price ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="text-xs text-gray-500">/{{ $product->baseUnit->unit->name ?? 'Unit' }}</span>
                </div>
                
                <!-- Unit Selector -->
                @if($product->units->count() > 1)
                <div x-data="{ selectedUnit: {{ $product->baseUnit->id }}, units: {{ json_encode($product->units->map(function($unit) {
                    return [
                        'id' => $unit->id,
                        'name' => $unit->unit->name,
                        'price' => $unit->price,
                        'conversion' => $unit->conversion_to_base,
                        'wholesale_prices' => $unit->wholesalePrices
                    ];
                })) }} }" class="relative">
                    <select x-model="selectedUnit" class="w-full text-sm border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($product->units as $unit)
                        <option value="{{ $unit->id }}">
                            {{ $unit->unit->name }} (Rp {{ number_format($unit->price, 0, ',', '.') }})
                        </option>
                        @endforeach
                    </select>
                    
                    <!-- Display selected unit price -->
                    <template x-if="selectedUnit">
                        <div class="text-xs text-gray-500 mt-1">
                            Konversi: <span x-text="units.find(u => u.id == selectedUnit)?.conversion"></span> satuan dasar
                        </div>
                    </template>
                </div>
                @endif

                <!-- Add to Cart -->
                @if($product->stock_in_base_unit > 0)
                <!-- PERBAIKI form ini -->
                <form action="{{ route('cart.add') }}" method="POST" class="cart-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="unit_id" value="{{ $product->baseUnit->id }}">
                    <input type="hidden" name="quantity" value="1">
                    
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-3 rounded-xl hover:shadow-lg transform hover:scale-105 transition-all-300 flex items-center justify-center gap-2 text-sm">
                        🛒 Tambah Keranjang
                    </button>
                </form>
                @else
                <button disabled 
                        class="w-full bg-gray-200 text-gray-500 font-bold py-3 rounded-xl cursor-not-allowed text-sm">
                    Stok Habis
                </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <!-- Empty State -->
    <div class="col-span-full text-center py-16">
        <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <h3 class="text-xl font-black text-gray-900 mb-3">Tidak Ada Produk Ditemukan</h3>
        <p class="text-gray-500 mb-8 max-w-sm mx-auto">
            @if(request('search'))
                Tidak ada hasil untuk "{{ request('search') }}"
            @elseif(request('category'))
                Tidak ada produk dalam kategori ini
            @else
                Belum ada produk yang tersedia
            @endif
        </p>
        <a href="{{ route('home') }}" 
           class="inline-flex items-center gap-2 bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 shadow-lg transition-all">
            🔍 Lihat Semua Produk
        </a>
    </div>
    @endforelse
</div>