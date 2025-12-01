@extends('layouts.customer')

@section('content')
<div class="pb-24 bg-white min-h-screen">
    <!-- Product Image -->
    <div class="relative bg-gradient-to-br from-gray-50 to-gray-100 h-96 w-full flex items-center justify-center overflow-hidden">
        @if($product->foto_produk)
            <img src="{{ Storage::url($product->foto_produk) }}" class="w-full h-full object-cover">
        @else
            <div class="text-center">
                <svg class="w-24 h-24 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-400 text-sm mt-2">Gambar tidak tersedia</p>
            </div>
        @endif
        
        <!-- Back Button -->
        <a href="{{ route('home') }}" class="absolute top-6 left-4 bg-white/90 backdrop-blur-sm p-3 rounded-2xl text-gray-800 hover:bg-white shadow-lg hover:shadow-xl transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        
        <!-- Stock Badge -->
        @if($product->stock_in_base_unit == 0)
            <div class="absolute top-6 right-4 bg-red-600 text-white px-3 py-2 rounded-2xl text-xs font-bold shadow-lg">
                STOK HABIS
            </div>
        @elseif($product->stock_in_base_unit < 10)
            <div class="absolute top-6 right-4 bg-orange-500 text-white px-3 py-2 rounded-2xl text-xs font-bold shadow-lg">
                STOK TERBATAS
            </div>
        @endif
    </div>

    <!-- Product Info -->
    <div class="p-6 -mt-8 bg-white rounded-t-3xl relative z-10 shadow-lg">
        <!-- Category & Code -->
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-3 py-1.5 rounded-full">
                {{ $product->category->name ?? 'Umum' }}
            </span>
            <div class="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-1 rounded">
                {{ $product->kode_produk }}
            </div>
        </div>

        <!-- Product Name -->
        <h1 class="text-2xl font-black text-gray-900 leading-tight mb-2">{{ $product->name }}</h1>

        <!-- Unit Selection & Price -->
        <div class="mt-6" x-data="{
            selectedUnit: {{ $product->baseUnit->id }},
            price: {{ $product->baseUnit->price }},
            unitName: '{{ $product->baseUnit->unit->name }}',
            stock: {{ floor($product->stock_in_base_unit / $product->baseUnit->conversion_to_base) }},
            conversion: {{ $product->baseUnit->conversion_to_base }}
        }">
            
            <!-- Unit Selection -->
            <label class="block text-sm font-bold text-gray-700 mb-3">Pilih Kemasan:</label>
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($product->units as $unit)
                <button 
                    type="button"
                    @click="selectedUnit = {{ $unit->id }}; 
                           price = {{ $unit->price }}; 
                           unitName = '{{ $unit->unit->name }}';
                           stock = Math.floor({{ $product->stock_in_base_unit }} / {{ $unit->conversion_to_base }});
                           conversion = {{ $unit->conversion_to_base }}"
                    :class="selectedUnit == {{ $unit->id }} ? 
                           'ring-2 ring-indigo-600 bg-indigo-50 text-indigo-700 border-indigo-200' : 
                           'bg-white border border-gray-300 text-gray-600 hover:border-gray-400'"
                    class="px-4 py-3 rounded-xl text-sm font-bold transition-all shadow-sm hover:shadow-md"
                >
                    {{ $unit->unit->name }}
                </button>
                @endforeach
            </div>

            <!-- Dynamic Price & Stock -->
            <div class="flex items-end justify-between mb-6">
                <div>
                    <div class="text-sm font-bold text-gray-500 mb-1">Harga:</div>
                    <div class="text-3xl font-black text-gray-900">
                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(price)"></span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1" x-text="'Stok tersedia: ' + stock + ' ' + unitName"></div>
                </div>
                
                <!-- Quantity Selector -->
                <div class="text-right">
                    <div class="text-sm font-bold text-gray-500 mb-2">Jumlah:</div>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="if($refs.quantity.value > 1) $refs.quantity.value--" 
                            class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 font-bold text-lg"
                            :disabled="$refs.quantity.value <= 1"
                            :class="$refs.quantity.value <= 1 ? 'opacity-30 cursor-not-allowed' : ''"
                        >-</button>
                        
                        <input 
                            x-ref="quantity"
                            type="number" 
                            value="1" 
                            min="1" 
                            :max="stock"
                            class="w-16 text-center border-0 bg-transparent text-lg font-bold text-gray-900 focus:ring-0"
                            readonly
                        >
                        
                        <button 
                            @click="if($refs.quantity.value < stock) $refs.quantity.value++" 
                            class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 font-bold text-lg"
                            :disabled="$refs.quantity.value >= stock"
                            :class="$refs.quantity.value >= stock ? 'opacity-30 cursor-not-allowed' : ''"
                        >+</button>
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($product->description)
            <div class="border-t border-gray-100 pt-6 mt-6">
                <h3 class="font-bold text-gray-800 mb-3 text-lg">Deskripsi Produk</h3>
                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
            </div>
            @endif

            <!-- Add to Cart Form -->
            <form action="{{ route('cart.add') }}" method="POST" class="mt-8">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="unit_id" x-model="selectedUnit">
                <input type="hidden" name="quantity" x-ref="quantityInput" :value="$refs.quantity ? $refs.quantity.value : 1">
                
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-4 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all flex items-center justify-center gap-3 text-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="stock == 0"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span x-text="stock == 0 ? 'STOK HABIS' : 'Tambah ke Keranjang'"></span>
                </button>
                
                <!-- Stock Warning -->
                <div x-show="stock > 0 && stock < 10" class="mt-3 p-3 bg-orange-50 border border-orange-200 rounded-xl">
                    <div class="flex items-center gap-2 text-orange-700 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Stok terbatas! Segera pesan sebelum kehabisan.
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productPage', () => ({
        init() {
            // Update hidden input when quantity changes
            this.$watch('$refs.quantity.value', (value) => {
                this.$refs.quantityInput.value = value;
            });
        }
    }));
});
</script>
@endsection