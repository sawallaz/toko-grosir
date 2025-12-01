@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gray-50 pb-6">
    <!-- Header -->
    <div class="bg-white p-6 border-b border-gray-200 shadow-sm sticky top-0 z-40">
        <h1 class="text-2xl font-black text-gray-900 flex items-center gap-3">
            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            Pesanan Saya
        </h1>
        <p class="text-sm text-gray-500 mt-1">Kelola dan lacak pesanan Anda</p>
    </div>

    @if(session('success'))
        <div class="mx-4 mt-4 p-4 bg-green-50 border border-green-200 rounded-2xl text-green-700 text-sm font-medium">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mx-4 mt-4 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-sm font-medium">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <div class="p-4 space-y-4">
        @forelse($orders as $order)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all">
            <!-- Header Order -->
            <div class="p-5 border-b border-gray-100">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="text-xs font-bold text-indigo-600 uppercase tracking-wide mb-1">INVOICE</div>
                        <div class="font-mono font-black text-gray-900 text-lg">{{ $order->invoice_number }}</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase
                            {{ $order->status == 'completed' ? 'bg-green-100 text-green-700' : 
                              ($order->status == 'pending' ? 'bg-yellow-100 text-yellow-700' : 
                              ($order->status == 'process' ? 'bg-blue-100 text-blue-700' : 
                              ($order->status == 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'))) }}">
                            {{ $order->status }}
                        </span>
                    </div>
                </div>
                
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <div class="flex items-center gap-4">
                        <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                        <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                        <span>{{ $order->total_items }} barang</span>
                    </div>
                    <span class="font-medium">Metode: {{ strtoupper($order->payment_method) }}</span>
                </div>
            </div>

            <!-- Order Info -->
            <div class="p-5">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-xs text-gray-500">Total Pembayaran</div>
                        <div class="text-2xl font-black text-gray-900 mt-1">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <a href="{{ route('orders.show', $order->id) }}" 
                           class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Detail
                        </a>
                        
                        @if($order->status === 'pending')
                        <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm('Batalkan pesanan ini? Tindakan tidak dapat dibatalkan.')"
                                    class="bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-red-700 transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Batal
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Belum Ada Pesanan</h3>
            <p class="text-gray-500 mb-8 max-w-sm mx-auto">Anda belum memiliki riwayat pesanan. Yuk mulai belanja!</p>
            <a href="{{ route('home') }}" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 shadow-lg transition-all inline-flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Belanja Sekarang
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection