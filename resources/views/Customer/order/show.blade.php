@extends('layouts.customer')

@section('content')
<div class="pb-24 bg-gray-50 min-h-screen">
    
    <!-- Header Invoice -->
    <div class="bg-white p-6 border-b border-gray-200 shadow-sm mb-4 sticky top-14 z-30">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-2xl font-black text-indigo-900">Invoice</h1>
                <div class="font-mono text-gray-500 text-sm">{{ $order->invoice_number }}</div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                {{ $order->status == 'completed' ? 'bg-green-100 text-green-700' : 
                  ($order->status == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                {{ $order->status }}
            </span>
        </div>
        <div class="text-xs text-gray-500 flex justify-between">
            <span>{{ $order->created_at->format('d F Y, H:i') }}</span>
            <span>Metode: {{ strtoupper($order->payment_method) }}</span>
        </div>
    </div>

    <!-- List Barang -->
    <div class="px-4 space-y-3">
        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Rincian Pesanan</h3>
        
        @foreach($order->details as $detail)
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center">
            <div>
                <div class="font-bold text-gray-800 text-sm">{{ $detail->productUnit->product->name ?? 'Produk Dihapus' }}</div>
                <div class="text-xs text-gray-500 mt-0.5">
                    {{ $detail->quantity }} {{ $detail->productUnit->unit->name ?? '' }} x Rp {{ number_format($detail->price_at_purchase, 0, ',', '.') }}
                </div>
            </div>
            <div class="font-bold text-indigo-600">
                Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
            </div>
        </div>
        @endforeach
    </div>

    <!-- Total Footer -->
    <div class="fixed bottom-16 left-0 w-full bg-white border-t border-gray-200 p-5 z-40 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
        <div class="max-w-md mx-auto flex justify-between items-center">
            <div class="text-xs font-bold text-gray-500 uppercase">Total Bayar</div>
            <div class="text-3xl font-black text-gray-900 tracking-tighter">
                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
            </div>
        </div>
        
        <a href="{{ route('orders.index') }}" class="mt-4 block w-full bg-gray-100 text-gray-600 text-center py-3 rounded-xl font-bold hover:bg-gray-200 transition">
            Kembali ke Riwayat
        </a>
    </div>

</div>
@endsection