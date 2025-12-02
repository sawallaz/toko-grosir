@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gray-50 pb-32 fade-in">
    <!-- Header -->
    <div class="bg-white p-6 border-b border-gray-200 shadow-sm sticky top-0 z-40">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700">
                    ←
                </a>
                <div>
                    <h1 class="text-xl font-black text-gray-900">Keranjang Belanja</h1>
                    <p class="text-sm text-gray-500" id="cart-count">{{ count(session('cart', [])) }} item</p>
                </div>
            </div>
            @if(session('cart') && count(session('cart')) > 0)
            <form action="{{ route('cart.clear') }}" method="POST" id="clear-cart-form">
                @csrf @method('DELETE')
                <button type="button" onclick="confirmClearCart()" 
                        class="text-red-500 hover:text-red-700 text-sm font-bold">
                    Hapus Semua
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('cart') && count(session('cart')) > 0)
        <!-- List Items -->
        <div class="p-4 space-y-3" id="cart-items">
            @foreach(session('cart') as $key => $item)
            <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-4 cart-item" data-key="{{ $key }}">
                <div class="flex gap-4">
                    <!-- Gambar -->
                    <div class="h-20 w-20 bg-gray-100 rounded-xl flex-shrink-0 overflow-hidden">
                        @if($item['image'])
                            <img src="{{ Storage::url($item['image']) }}" class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                📦
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 text-sm leading-tight line-clamp-2">{{ $item['product_name'] }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ $item['unit_name'] }}</p>
                                <div class="text-sm font-bold text-indigo-600 mt-1">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                </div>
                            </div>
                            <button type="button" onclick="removeFromCart('{{ $key }}')" class="text-gray-400 hover:text-red-500 transition-colors">
                                ✕
                            </button>
                        </div>

                        <div class="flex items-end justify-between mt-4">
                            <!-- Quantity Controls -->
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="updateQuantity('{{ $key }}', -1)" 
                                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 disabled:opacity-30">
                                    −
                                </button>
                                
                                <span class="text-sm font-bold text-gray-900 min-w-8 text-center quantity-display">{{ $item['quantity'] }}</span>
                                
                                <button type="button" onclick="updateQuantity('{{ $key }}', 1)"
                                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200">
                                    +
                                </button>
                            </div>
                            
                            <div class="text-right">
                                <div class="text-lg font-black text-indigo-600 subtotal">
                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Total & Checkout -->
        <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-6 z-50 shadow-hard">
            <div class="max-w-md mx-auto">
                <!-- Summary -->
                <div class="flex justify-between items-center mb-4">
                    <div class="text-sm text-gray-600">Total (<span id="total-items">{{ count(session('cart')) }}</span> item)</div>
                    <div class="text-2xl font-black text-gray-900" id="cart-total">
                        Rp {{ number_format($total, 0, ',', '.') }}
                    </div>
                </div>
                
                <!-- Checkout Button -->
                <a href="{{ route('checkout.form') }}" 
                   class="block w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center py-4 rounded-2xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all-300">
                    Lanjutkan Checkout
                </a>
                
                <!-- Continue Shopping -->
                <a href="{{ route('home') }}" class="block text-center text-gray-500 hover:text-gray-700 text-sm font-medium mt-3">
                    + Tambah Produk Lainnya
                </a>
            </div>
        </div>

    @else
        <!-- Empty State -->
        <div class="flex flex-col items-center justify-center py-20 px-4 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6">
                🛒
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Keranjang Kosong</h3>
            <p class="text-gray-500 mb-8 max-w-sm">Belum ada produk di keranjang belanja Anda</p>
            <a href="{{ route('home') }}" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 shadow-lg transition-all">
                Jelajahi Produk
            </a>
        </div>
    @endif
</div>

<script>
async function updateQuantity(key, change) {
    const itemElement = document.querySelector(`.cart-item[data-key="${key}"]`);
    const quantityDisplay = itemElement.querySelector('.quantity-display');
    const subtotalElement = itemElement.querySelector('.subtotal');
    const currentQty = parseInt(quantityDisplay.textContent);
    const newQty = currentQty + change;
    
    if (newQty < 1) {
        removeFromCart(key);
        return;
    }
    
    try {
        const response = await fetch(`/cart/update/${key}`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: newQty })
        });
        
        const data = await response.json();
        
        if (data.success) {
            quantityDisplay.textContent = newQty;
            subtotalElement.textContent = `Rp ${formatNumber(data.subtotal)}`;
            updateCartTotal(data.total);
            updateItemCount(data.itemCount);
        } else {
            alert(data.message || 'Gagal memperbarui jumlah');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    }
}

async function removeFromCart(key) {
    if (!confirm('Hapus item dari keranjang?')) return;
    
    try {
        const response = await fetch(`/cart/remove/${key}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.querySelector(`.cart-item[data-key="${key}"]`).remove();
            updateCartTotal(data.total);
            updateItemCount(data.itemCount);
            
            if (data.itemCount === 0) {
                location.reload(); // Reload to show empty state
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function confirmClearCart() {
    if (confirm('Hapus semua item dari keranjang?')) {
        document.getElementById('clear-cart-form').submit();
    }
}

function updateCartTotal(total) {
    document.getElementById('cart-total').textContent = `Rp ${formatNumber(total)}`;
}

function updateItemCount(count) {
    document.getElementById('cart-count').textContent = `${count} item`;
    document.getElementById('total-items').textContent = count;
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>
@endsection