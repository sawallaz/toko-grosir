@extends('layouts.customer')

@section('content')
<div class="p-4 pb-24">
    <h1 class="text-xl font-black text-gray-800 mb-4 flex items-center gap-2">
        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
        Pesanan Saya
    </h1>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-xl text-sm font-bold">
            {{ session('success') }}
        </div>
    @endif

    @forelse($orders as $order)
        <a href="{{ route('orders.show', $order->id) }}" class="block bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-3 hover:shadow-md transition">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <div class="font-bold text-indigo-600 text-xs mb-1">INVOICE</div>
                    <div class="font-mono font-bold text-gray-800">{{ $order->invoice_number }}</div>
                </div>
                <div class="text-right">
                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase
                        {{ $order->status == 'completed' ? 'bg-green-100 text-green-700' : 
                          ($order->status == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                        {{ $order->status }}
                    </span>
                </div>
            </div>
            
            <div class="flex justify-between items-end mt-2">
                <div class="text-xs text-gray-500">
                    {{ $order->created_at->format('d M Y H:i') }}
                    <br>
                    {{ $order->total_items }} Barang
                </div>
                <div class="text-lg font-black text-gray-900">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </div>
            </div>
        </a>
    @empty
        <div class="text-center py-20 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            <p>Belum ada riwayat pesanan.</p>
            <a href="{{ route('home') }}" class="text-indigo-600 font-bold hover:underline mt-2 inline-block">Belanja Sekarang</a>
        </div>
    @endforelse
</div>
@endsection