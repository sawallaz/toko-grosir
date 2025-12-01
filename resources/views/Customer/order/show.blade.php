@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gray-50 pb-32">
    <!-- Header -->
    <div class="bg-white p-6 border-b border-gray-200 shadow-sm sticky top-0 z-40">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('orders.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-gray-900">Detail Pesanan</h1>
                    <div class="font-mono text-gray-500 text-sm">{{ $order->invoice_number }}</div>
                </div>
            </div>
            <span class="px-3 py-2 rounded-full text-xs font-bold uppercase
                {{ $order->status == 'completed' ? 'bg-green-100 text-green-700 border border-green-200' : 
                  ($order->status == 'pending' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : 
                  ($order->status == 'process' ? 'bg-blue-100 text-blue-700 border border-blue-200' : 
                  ($order->status == 'cancelled' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-gray-100 text-gray-700 border border-gray-200'))) }}">
                {{ $order->status }}
            </span>
        </div>
        
        <div class="flex justify-between items-center text-xs text-gray-500">
            <div class="flex items-center gap-4">
                <span>{{ $order->created_at->format('d F Y, H:i') }}</span>
                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                <span>Metode: {{ strtoupper($order->payment_method) }}</span>
            </div>
            <span class="font-medium">{{ $order->delivery_type == 'delivery' ? 'Diantar' : 'Ambil di Toko' }}</span>
        </div>

        <!-- Cancel Button for Pending Orders -->
        @if($order->status === 'pending')
        <div class="mt-4 pt-4 border-t border-gray-100">
            <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Batalkan pesanan ini? Pesanan akan dihapus dan tidak dapat dikembalikan.')"
                        class="w-full bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 transition-all flex items-center justify-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Batalkan Pesanan
                </button>
            </form>
            <p class="text-xs text-gray-500 text-center mt-2">
                Anda bisa membatalkan pesanan selama status masih "Pending"
            </p>
        </div>
        @endif
    </div>

    <!-- Order Items -->
    <div class="p-4">
        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            Rincian Pesanan
        </h3>
        
        <div class="space-y-3">
            @foreach($order->details as $detail)
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="font-bold text-gray-900 text-sm mb-1">{{ $detail->productUnit->product->name ?? 'Produk Dihapus' }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $detail->quantity }} {{ $detail->productUnit->unit->name ?? '' }} × 
                            Rp {{ number_format($detail->price_at_purchase, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-indigo-600 text-lg">
                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Order Summary -->
    <div class="p-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Ringkasan Pembayaran
            </h3>
            
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Subtotal ({{ $order->total_items }} barang)</span>
                    <span class="font-medium">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                
                @if($order->delivery_type === 'delivery')
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Biaya Pengiriman</span>
                    <span class="font-medium text-green-600">GRATIS</span>
                </div>
                @endif
                
                <div class="border-t border-gray-200 pt-3 mt-2">
                    <div class="flex justify-between text-lg font-black">
                        <span>Total Bayar</span>
                        <span class="text-indigo-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Info -->
    @if($order->delivery_address)
    <div class="p-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Alamat Pengiriman
            </h3>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $order->delivery_address }}</p>
            @if($order->delivery_note)
            <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                <div class="text-xs font-medium text-gray-500 mb-1">Catatan:</div>
                <div class="text-sm text-gray-700">{{ $order->delivery_note }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Fixed Bottom Navigation -->
<div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-6 z-50 shadow-2xl">
    <div class="max-w-md mx-auto flex justify-between items-center">
        <div>
            <div class="text-xs font-bold text-gray-500 uppercase">Total Bayar</div>
            <div class="text-2xl font-black text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
        </div>
        <a href="{{ route('orders.index') }}" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition-all flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
    </div>
</div>
@endsection