@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gray-50 pb-32">
    <!-- Header -->
    <div class="bg-white p-6 border-b border-gray-200 shadow-sm sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <a href="{{ route('cart.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-black text-gray-900">Checkout</h1>
                <p class="text-sm text-gray-500">Lengkapi data pesanan</p>
            </div>
        </div>
    </div>

    <form action="{{ route('checkout.form') }}" method="POST">
        @csrf
        
        <div class="p-4 space-y-6">
            <!-- Metode Pengiriman -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Metode Pengiriman
                </h3>
                
                <div class="space-y-3">
                    <label class="flex items-center gap-4 p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-400 cursor-pointer transition-all">
                        <input type="radio" name="delivery_type" value="pickup" class="text-indigo-600 focus:ring-indigo-500" checked>
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">Ambil di Toko</div>
                            <div class="text-sm text-gray-500 mt-1">Gratis biaya pengiriman</div>
                        </div>
                        <div class="text-lg font-black text-green-600">GRATIS</div>
                    </label>
                    
                    <label class="flex items-center gap-4 p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-400 cursor-pointer transition-all">
                        <input type="radio" name="delivery_type" value="delivery" class="text-indigo-600 focus:ring-indigo-500">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">Antar ke Alamat</div>
                            <div class="text-sm text-gray-500 mt-1">Dengan kurir kami</div>
                        </div>
                        <div class="text-lg font-black text-orange-600">Rp 10.000</div>
                    </label>
                </div>
            </div>

            <!-- Catatan Pesanan -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Catatan Pesanan (Opsional)
                </h3>
                <textarea name="delivery_note" rows="3" placeholder="Contoh: Warna spesifik, permintaan khusus, dll." 
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"></textarea>
            </div>

            <!-- Ringkasan Pesanan -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Ringkasan Pesanan
                </h3>
                
                <div class="space-y-3">
                    @foreach($cart as $item)
                    <div class="flex items-center gap-3 py-2">
                        <div class="h-12 w-12 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                            @if($item['image'])
                                <img src="{{ Storage::url($item['image']) }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center bg-gray-200">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 text-sm line-clamp-1">{{ $item['product_name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $item['quantity'] }} x {{ $item['unit_name'] }}</div>
                        </div>
                        <div class="text-sm font-bold text-gray-900">
                            Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-200 mt-4 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Biaya Pengiriman</span>
                        <span class="font-medium text-green-600" id="shippingCost">GRATIS</span>
                    </div>
                    <div class="flex justify-between text-lg font-black pt-2 border-t border-gray-200">
                        <span>Total Bayar</span>
                        <span id="totalAmount">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Button -->
        <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-6 z-50 shadow-2xl">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Buat Pesanan
            </button>
            <p class="text-center text-xs text-gray-500 mt-2">
                Dengan membuat pesanan, Anda menyetujui syarat dan ketentuan kami
            </p>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliveryRadios = document.querySelectorAll('input[name="delivery_type"]');
    const shippingCost = document.getElementById('shippingCost');
    const totalAmount = document.getElementById('totalAmount');
    const baseTotal = {{ $total }};
    const shippingFee = 10000;

    function updateTotals() {
        const selectedDelivery = document.querySelector('input[name="delivery_type"]:checked').value;
        
        if (selectedDelivery === 'delivery') {
            shippingCost.textContent = 'Rp ' + shippingFee.toLocaleString('id-ID');
            const newTotal = baseTotal + shippingFee;
            totalAmount.textContent = 'Rp ' + newTotal.toLocaleString('id-ID');
        } else {
            shippingCost.textContent = 'GRATIS';
            totalAmount.textContent = 'Rp ' + baseTotal.toLocaleString('id-ID');
        }
    }

    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', updateTotals);
    });

    // Initialize
    updateTotals();
});
</script>
@endsection