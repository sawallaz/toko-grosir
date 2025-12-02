<div class="p-6">
    <div class="flex justify-between items-start mb-6">
        <h3 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h3>
        <button data-close-modal class="text-gray-500 hover:text-gray-700">
            ✕
        </button>
    </div>
    
    <div class="grid md:grid-cols-2 gap-8">
        <!-- Image -->
        <div class="aspect-square rounded-2xl overflow-hidden bg-gray-100">
            @if($product->foto_produk)
                <img src="{{ Storage::url($product->foto_produk) }}" 
                     class="w-full h-full object-cover"
                     alt="{{ $product->name }}">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    📦
                </div>
            @endif
        </div>
        
        <!-- Details -->
        <div>
            <!-- Category & Stock -->
            <div class="flex items-center gap-4 mb-4">
                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm">
                    {{ $product->category->name ?? 'Umum' }}
                </span>
                @if($product->stock_in_base_unit > 0)
                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                        Stok: {{ floor($product->stock_in_base_unit / ($product->baseUnit->conversion_to_base ?? 1)) }}
                    </span>
                @else
                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">
                        Stok Habis
                    </span>
                @endif
            </div>
            
            <!-- Price -->
            <div class="mb-6">
                <div class="text-sm text-gray-500">Harga:</div>
                <div class="text-3xl font-black text-indigo-600">
                    Rp {{ number_format($product->baseUnit->price ?? 0, 0, ',', '.') }}
                    <span class="text-lg text-gray-500">/{{ $product->baseUnit->unit->name ?? 'Unit' }}</span>
                </div>
            </div>
            
            <!-- Unit Selection -->
            @if($product->units->count() > 1)
            <div class="mb-6">
                <div class="text-sm font-medium text-gray-700 mb-2">Pilih Satuan:</div>
                <div class="space-y-2">
                    @foreach($product->units as $unit)
                    <label class="flex items-center justify-between p-3 border rounded-xl hover:border-indigo-400 cursor-pointer">
                        <div>
                            <div class="font-medium">{{ $unit->unit->name }}</div>
                            <div class="text-sm text-gray-500">
                                Konversi: {{ $unit->conversion_to_base }} satuan dasar
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-indigo-600">
                                Rp {{ number_format($unit->price, 0, ',', '.') }}
                            </div>
                            @if($unit->wholesalePrices->count() > 0)
                            <div class="text-xs text-gray-500">
                                Harga grosir tersedia
                            </div>
                            @endif
                        </div>
                        <input type="radio" name="unit_id" value="{{ $unit->id }}" 
                               class="text-indigo-600" 
                               {{ $unit->is_base_unit ? 'checked' : '' }}>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- Add to Cart Form -->
            @if($product->stock_in_base_unit > 0)
            <form action="{{ route('cart.add') }}" method="POST" class="quick-view-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="unit_id" value="{{ $product->baseUnit->id }}">
                
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center border rounded-xl">
                        <button type="button" class="px-4 py-2 text-lg" onclick="changeQty(-1)">−</button>
                        <input type="number" name="quantity" value="1" min="1" 
                               class="w-16 text-center border-0 focus:ring-0" 
                               id="quick-qty">
                        <button type="button" class="px-4 py-2 text-lg" onclick="changeQty(1)">+</button>
                    </div>
                    <div class="text-sm text-gray-500">
                        Stok tersedia: {{ floor($product->stock_in_base_unit / ($product->baseUnit->conversion_to_base ?? 1)) }}
                    </div>
                </div>
                
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-xl transition-all">
                    🛒 Tambah ke Keranjang
                </button>
            </form>
            @else
            <button disabled 
                    class="w-full bg-gray-200 text-gray-500 py-4 rounded-xl font-bold text-lg cursor-not-allowed">
                Stok Habis
            </button>
            @endif
        </div>
    </div>
</div>

<script>
function changeQty(change) {
    const input = document.getElementById('quick-qty');
    let value = parseInt(input.value) + change;
    if (value < 1) value = 1;
    input.value = value;
}

// Handle unit selection
document.querySelectorAll('input[name="unit_id"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelector('input[name="unit_id"]').value = this.value;
    });
});
</script>