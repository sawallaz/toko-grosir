@extends('layouts.customer')

@section('title', 'Pembayaran Berhasil - FADLIMART')
@section('content')
<div class="min-h-screen bg-gradient-to-b from-green-50 to-white flex flex-col items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        <!-- Success Icon -->
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        
        <!-- Success Message -->
        <h1 class="text-3xl font-black text-gray-900 mb-4">Pembayaran Berhasil!</h1>
        <p class="text-gray-600 mb-8">
            Terima kasih telah berbelanja di FADLIMART. Pesanan Anda sedang diproses.
        </p>
        
        <!-- Order Details -->
        <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-6 mb-6">
            <div class="text-center mb-4">
                <div class="text-xs text-gray-500">Kode Pesanan</div>
                <div class="font-mono font-bold text-xl text-gray-900">{{ $transaction->invoice_number }}</div>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Tanggal</span>
                    <span class="font-medium">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Pembayaran</span>
                    <span class="font-bold text-green-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status</span>
                    <span class="font-bold text-green-600">Dibayar</span>
                </div>
            </div>
        </div>
        
        <!-- Next Steps -->
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mb-8">
            <h3 class="font-bold text-blue-800 mb-3">Apa Selanjutnya?</h3>
            <ul class="text-sm text-blue-700 space-y-2 text-left">
                <li class="flex items-start gap-2">
                    <span class="font-bold">📧</span>
                    <span>Anda akan menerima email konfirmasi</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="font-bold">📱</span>
                    <span>Notifikasi WhatsApp akan dikirim</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="font-bold">📦</span>
                    <span>Pesanan akan diproses dalam 1x24 jam</span>
                </li>
            </ul>
        </div>
        
        <!-- Action Buttons -->
        <div class="space-y-4">
            <a href="{{ route('orders.show', $transaction->id) }}" 
               class="block w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold text-lg hover:bg-indigo-700 transition-all">
                Lihat Detail Pesanan
            </a>
            
            <a href="{{ route('orders.index') }}" 
               class="block w-full bg-white border-2 border-indigo-600 text-indigo-600 py-4 rounded-2xl font-bold text-lg hover:bg-indigo-50 transition-all">
                Lihat Semua Pesanan
            </a>
            
            <a href="{{ route('home') }}" 
               class="block w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-200 transition-all">
                Lanjutkan Belanja
            </a>
        </div>
        
        <!-- Contact Support -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-2">Butuh bantuan?</p>
            <div class="flex justify-center gap-4">
                <a href="https://wa.me/6281234567890" target="_blank" 
                   class="inline-flex items-center gap-2 text-green-600 font-medium">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.206-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.76.982.998-3.677-.236-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.9 6.994c-.004 5.45-4.438 9.88-9.888 9.88m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.333.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.333 11.893-11.893 0-3.18-1.24-6.162-3.495-8.411"></path>
                    </svg>
                    WhatsApp CS
                </a>
                <a href="mailto:cs@fadlimart.com" class="inline-flex items-center gap-2 text-blue-600 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Email CS
                </a>
            </div>
        </div>
    </div>
</div>
@endsection