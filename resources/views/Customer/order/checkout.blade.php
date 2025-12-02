<!-- customer/order/checkout.blade.php -->
@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gray-50 pb-32 fade-in">
    <!-- Header -->
    <div class="bg-white p-6 border-b border-gray-200 shadow-sm sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <a href="{{ route('cart.index') }}" class="text-gray-500 hover:text-gray-700">
                ←
            </a>
            <div>
                <h1 class="text-xl font-black text-gray-900">Checkout</h1>
                <p class="text-sm text-gray-500">Lengkapi data pesanan</p>
            </div>
        </div>
    </div>

    <form action="{{ route('checkout') }}" method="POST" id="checkout-form">
        @csrf
        
        <div class="p-4 space-y-6">
            <!-- Customer Info -->
            <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    👤 Informasi Pemesan
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" value="{{ Auth::user()->name }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl" readonly>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" value="{{ Auth::user()->email }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl" readonly>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon <span class="text-red-500">*</span></label>
                        <input type="tel" name="customer_phone" 
                               value="{{ old('customer_phone', Auth::user()->phone ?? '') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="0812-3456-7890"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Digunakan untuk notifikasi pembayaran</p>
                    </div>
                </div>
            </div>

            <!-- Delivery Method -->
            <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    🚚 Metode Pengiriman
                </h3>
                
                <div class="space-y-3">
                    <label class="flex items-center gap-4 p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-400 cursor-pointer transition-all-300">
                        <input type="radio" name="delivery_type" value="pickup" class="text-indigo-600 focus:ring-indigo-500" checked>
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">Ambil di Toko</div>
                            <div class="text-sm text-gray-500 mt-1">Jl. Contoh No. 123, Kota Anda</div>
                        </div>
                        <div class="text-lg font-black text-green-600">GRATIS</div>
                    </label>
                    
                    <label class="flex items-center gap-4 p-4 border-2 border-gray-200 rounded-xl hover:border-indigo-400 cursor-pointer transition-all-300">
                        <input type="radio" name="delivery_type" value="delivery" class="text-indigo-600 focus:ring-indigo-500">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">Antar ke Alamat</div>
                            <div class="text-sm text-gray-500 mt-1">Dengan kurir kami</div>
                        </div>
                        <div class="text-lg font-black text-orange-600" id="delivery-fee">Rp 10.000</div>
                    </label>
                </div>
            </div>

            <!-- Delivery Address (Conditional) -->
            <div id="address-section" class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5 hidden">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    📍 Alamat Pengiriman <span class="text-red-500">*</span>
                </h3>
                <textarea name="delivery_address" rows="3" 
                          placeholder="Masukkan alamat lengkap pengiriman (nama jalan, nomor rumah, RT/RW, kecamatan, kota)" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none">{{ old('delivery_address', Auth::user()->address ?? '') }}</textarea>
            </div>

            <!-- Order Notes -->
            <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    📝 Catatan Pesanan (Opsional)
                </h3>
                <textarea name="delivery_note" rows="3" placeholder="Contoh: Warna spesifik, permintaan khusus, dll." 
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none">{{ old('delivery_note') }}</textarea>
            </div>

            <!-- Payment Method -->
            <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    💳 Metode Pembayaran
                </h3>
                
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900">Midtrans Payment Gateway</div>
                            <div class="text-sm text-gray-600">Transfer Bank, E-Wallet, QRIS, dll.</div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="text-center">
                        <div class="w-10 h-10 mx-auto mb-1 bg-gray-100 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold">BCA</span>
                        </div>
                        <span class="text-xs">Transfer Bank</span>
                    </div>
                    <div class="text-center">
                        <div class="w-10 h-10 mx-auto mb-1 bg-gray-100 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold">QRIS</span>
                        </div>
                        <span class="text-xs">QR Code</span>
                    </div>
                    <div class="text-center">
                        <div class="w-10 h-10 mx-auto mb-1 bg-gray-100 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold">GOPAY</span>
                        </div>
                        <span class="text-xs">E-Wallet</span>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    📋 Ringkasan Pesanan
                </h3>
                
                <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                    @foreach($cart as $item)
                    <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
                        <div class="h-12 w-12 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                            @if($item['image'])
                                <img src="{{ Storage::url($item['image']) }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center bg-gray-200">
                                    📦
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 text-sm line-clamp-1">{{ $item['product_name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $item['quantity'] }} × {{ $item['unit_name'] }}</div>
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
                        <span class="font-medium text-green-600" id="shipping-cost">GRATIS</span>
                    </div>
                    <div class="flex justify-between text-lg font-black pt-2 border-t border-gray-200">
                        <span>Total Bayar</span>
                        <span id="total-amount">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Button -->
        <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-6 z-50 shadow-hard">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all-300 flex items-center justify-center gap-2">
                💳 Lanjutkan Pembayaran
            </button>
            <p class="text-center text-xs text-gray-500 mt-2">
                Anda akan diarahkan ke halaman pembayaran Midtrans
            </p>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliveryRadios = document.querySelectorAll('input[name="delivery_type"]');
    const addressSection = document.getElementById('address-section');
    const shippingCost = document.getElementById('shipping-cost');
    const totalAmount = document.getElementById('total-amount');
    const baseTotal = {{ $total }};
    const deliveryFee = 10000;

    // Handle delivery type change
    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'delivery') {
                addressSection.classList.remove('hidden');
                shippingCost.textContent = 'Rp ' + deliveryFee.toLocaleString('id-ID');
                updateTotal(baseTotal + deliveryFee);
                
                // Set required attribute
                document.querySelector('[name="delivery_address"]').required = true;
            } else {
                addressSection.classList.add('hidden');
                shippingCost.textContent = 'GRATIS';
                updateTotal(baseTotal);
                
                // Remove required attribute
                document.querySelector('[name="delivery_address"]').required = false;
            }
        });
    });

    // Form validation
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        const phone = document.querySelector('[name="customer_phone"]').value;
        const deliveryType = document.querySelector('[name="delivery_type"]:checked').value;
        const deliveryAddress = document.querySelector('[name="delivery_address"]');
        
        // Validate phone
        if (!phone.match(/^[0-9]{10,13}$/)) {
            e.preventDefault();
            alert('Nomor telepon harus 10-13 digit angka');
            return;
        }
        
        // Validate address if delivery
        if (deliveryType === 'delivery' && (!deliveryAddress.value || deliveryAddress.value.trim().length < 10)) {
            e.preventDefault();
            alert('Mohon masukkan alamat lengkap pengiriman (minimal 10 karakter)');
            return;
        }
        
        // Show loading
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin">⟳</span> Memproses...';
    });

    function updateTotal(amount) {
        totalAmount.textContent = 'Rp ' + amount.toLocaleString('id-ID');
    }
});
</script>
@endsection