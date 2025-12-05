@extends('layouts.customer')

@section('content')
<div class="p-4 pb-32" x-data="cartSystem()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-black text-gray-800">Keranjang Belanja</h1>
        <!-- Tombol Hapus Terpilih -->
        <button x-show="selectedItems.length > 0" @click="deleteSelected()" class="text-xs font-bold text-red-600 bg-red-50 px-3 py-1.5 rounded-lg border border-red-100">
            Hapus (<span x-text="selectedItems.length"></span>)
        </button>
    </div>

    @if(session('cart') && count(session('cart')) > 0)
        <!-- Form Hapus Masal -->
        <form id="bulkDeleteForm" action="{{ route('cart.remove', 'bulk') }}" method="POST" class="hidden">
            @csrf @method('DELETE')
            <input type="hidden" name="keys" :value="JSON.stringify(selectedItems)">
        </form>

        <div class="space-y-4">
            @foreach(session('cart') as $key => $item)
            <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex gap-3 items-center relative overflow-hidden">
                
                <!-- Checkbox Custom -->
                <label class="flex items-center cursor-pointer relative z-10">
                    <input type="checkbox" value="{{ $key }}" x-model="selectedItems" class="w-5 h-5 text-indigo-600 rounded-md border-gray-300 focus:ring-indigo-500">
                </label>

                <!-- Gambar -->
                <div class="h-20 w-20 bg-gray-50 rounded-xl flex-shrink-0 overflow-hidden border border-gray-200">
                    @if($item['image']) <img src="{{ Storage::url($item['image']) }}" class="h-full w-full object-cover">
                    @else <div class="h-full w-full flex items-center justify-center text-xs text-gray-400 font-bold">NO IMG</div> @endif
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <h3 class="font-bold text-gray-800 text-sm truncate pr-6">{{ $item['product_name'] }}</h3>
                        <!-- Hapus Single -->
                        <form action="{{ route('cart.remove', $key) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-gray-300 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </form>
                    </div>
                    <div class="text-xs text-gray-500 mb-2">{{ $item['unit_name'] }}</div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-indigo-700 font-black text-base">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                        
                        <!-- Qty Control (Submit on Change) -->
                        <form action="{{ route('cart.update', $key) }}" method="POST" class="flex items-center bg-gray-100 rounded-lg px-1 h-8">
                            @csrf
                            <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                            <button type="submit" class="w-6 text-gray-500 font-bold hover:text-indigo-600" {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>-</button>
                        </form>
                        <span class="text-sm font-bold mx-2">{{ $item['quantity'] }}</span>
                        <form action="{{ route('cart.update', $key) }}" method="POST" class="flex items-center bg-gray-100 rounded-lg px-1 h-8">
                            @csrf
                            <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                            <button type="submit" class="w-6 text-gray-500 font-bold hover:text-indigo-600">+</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Footer Checkout -->
        <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 z-40 shadow-[0_-5px_20px_rgba(0,0,0,0.1)] safe-area-pb">
            <div class="max-w-md mx-auto">
                <form action="{{ route('checkout') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Pilihan Bayar -->
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer relative">
                            <input type="radio" name="payment_method" value="online" class="peer sr-only" checked>
                            <div class="p-3 rounded-xl border-2 border-gray-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 text-center transition-all">
                                <div class="text-xs font-bold text-gray-500 uppercase mb-1">Transfer / QRIS</div>
                                <div class="font-black text-indigo-800">ONLINE</div>
                            </div>
                        </label>
                        <label class="cursor-pointer relative">
                            <input type="radio" name="payment_method" value="store" class="peer sr-only">
                            <div class="p-3 rounded-xl border-2 border-gray-200 peer-checked:border-green-600 peer-checked:bg-green-50 text-center transition-all">
                                <div class="text-xs font-bold text-gray-500 uppercase mb-1">Bayar Di Toko</div>
                                <div class="font-black text-green-800">CASH</div>
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-bold text-gray-400 uppercase">Total Bayar</div>
                            <div class="text-2xl font-black text-gray-900 tracking-tight">Rp {{ number_format($total, 0, ',', '.') }}</div>
                        </div>
                        <button type="submit" class="bg-gray-900 text-white px-8 py-3 rounded-xl font-bold shadow-lg transform active:scale-95 transition hover:bg-black">
                            Checkout
                        </button>
                    </div>
                </form>
            </div>
        </div>

    @else
        <div class="text-center py-20 text-gray-400">
            <svg class="w-24 h-24 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <p class="font-medium">Keranjang masih kosong.</p>
            <a href="{{ route('home') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2 rounded-full font-bold shadow hover:bg-indigo-700">Mulai Belanja</a>
        </div>
    @endif
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('cartSystem', () => ({
            selectedItems: [],
            deleteSelected() {
                if(confirm('Hapus item terpilih?')) {
                    // Submit form hidden
                    // Karena ini blade form, kita harus sesuaikan controller remove untuk handle array
                    // Atau kirim request satu-satu (kurang efisien).
                    // Di sini saya pakai form hidden manual di atas.
                    document.getElementById('bulkDeleteForm').submit();
                }
            }
        }))
    })
</script>
@endsection