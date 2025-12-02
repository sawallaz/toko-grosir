@extends('layouts.customer')

@section('title', 'Pembayaran - FADLIMART')
@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white pb-32">
    <!-- Header -->
    <div class="bg-white p-6 border-b border-gray-200 shadow-sm sticky top-0 z-40">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700">
                    ←
                </a>
                <div>
                    <h1 class="text-xl font-black text-gray-900">Pembayaran</h1>
                    <p class="text-sm text-gray-500">Selesaikan pembayaran Anda</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500">Kode Pesanan</div>
                <div class="font-mono font-bold text-gray-900">{{ $transaction->invoice_number }}</div>
            </div>
        </div>
    </div>

    <div class="p-4 space-y-6">
        <!-- Payment Status -->
        <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-900">Status Pembayaran</h3>
                    <p class="text-sm text-gray-500">Batas waktu: {{ $transaction->expired_at->format('d M Y, H:i') }}</p>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Total Pembayaran</div>
                    <div class="text-2xl font-black text-indigo-600">
                        Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                    </div>
                </div>
            </div>
            
            <!-- Status Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full 
                {{ $transaction->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                  ($transaction->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 
                  'bg-red-100 text-red-800') }}">
                <span class="w-2 h-2 rounded-full 
                    {{ $transaction->payment_status === 'pending' ? 'bg-yellow-600' : 
                      ($transaction->payment_status === 'paid' ? 'bg-green-600' : 
                      'bg-red-600') }}"></span>
                <span class="font-bold uppercase">{{ $transaction->payment_status === 'pending' ? 'Menunggu Pembayaran' : ucfirst($transaction->payment_status) }}</span>
            </div>
            
            <!-- Countdown Timer -->
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="text-sm text-gray-600 mb-2">Selesaikan dalam:</div>
                <div id="countdown-timer" class="text-2xl font-mono font-bold text-gray-900">
                    <span id="hours">00</span>:<span id="minutes">00</span>:<span id="seconds">00</span>
                </div>
            </div>
        </div>

        <!-- Midtrans Payment Gateway -->
        <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5">
            <h3 class="font-bold text-gray-900 mb-4">Pilih Metode Pembayaran</h3>
            <p class="text-sm text-gray-500 mb-6">Pilih metode pembayaran yang Anda inginkan</p>
            
            <!-- Midtrans Snap Embed -->
            <div id="snap-container" class="min-h-[400px]">
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4">
                        <div class="w-full h-full border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                    <p class="text-gray-600">Memuat halaman pembayaran...</p>
                </div>
            </div>
        </div>

        <!-- Payment Instructions -->
        <div class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5">
            <h3 class="font-bold text-gray-900 mb-4">Instruksi Pembayaran</h3>
            
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                        1
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">Pilih metode pembayaran</div>
                        <div class="text-sm text-gray-500">Pilih bank, e-wallet, atau metode lainnya</div>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                        2
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">Ikuti instruksi</div>
                        <div class="text-sm text-gray-500">Ikuti langkah-langkah yang diberikan</div>
                    </div>
                </div>
                
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                        3
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">Konfirmasi otomatis</div>
                        <div class="text-sm text-gray-500">Pesanan akan diproses otomatis setelah pembayaran berhasil</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5">
            <h4 class="font-bold text-yellow-800 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Masalah Pembayaran?
            </h4>
            <ul class="text-sm text-yellow-700 space-y-1">
                <li>• Pastikan saldo/cukup untuk pembayaran</li>
                <li>• Cek email/SMS untuk instruksi pembayaran</li>
                <li>• Hubungi CS jika terjadi kesalahan</li>
                <li>• Pembayaran kadaluarsa dalam 24 jam</li>
            </ul>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-6 z-50 shadow-hard">
        <div class="flex gap-4">
            <a href="{{ route('orders.index') }}" 
               class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-bold text-center hover:bg-gray-200 transition-all">
                Lihat Pesanan
            </a>
            <button onclick="checkPaymentStatus()" 
                    class="flex-1 bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition-all">
                Cek Status
            </button>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize countdown timer
    initializeCountdown();
    
    // Initialize Midtrans Snap
    initializeSnap();
    
    // Auto check payment status every 30 seconds
    setInterval(checkPaymentStatus, 30000);
});

function initializeCountdown() {
    const expiredAt = new Date('{{ $transaction->expired_at }}').getTime();
    
    function updateTimer() {
        const now = new Date().getTime();
        const distance = expiredAt - now;
        
        if (distance < 0) {
            document.getElementById('countdown-timer').innerHTML = "WAKTU HABIS";
            window.location.reload();
            return;
        }
        
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById('hours').innerText = hours.toString().padStart(2, '0');
        document.getElementById('minutes').innerText = minutes.toString().padStart(2, '0');
        document.getElementById('seconds').innerText = seconds.toString().padStart(2, '0');
    }
    
    updateTimer();
    setInterval(updateTimer, 1000);
}

function initializeSnap() {
    const snapToken = '{{ $transaction->midtrans_snap_token }}';
    
    if (!snapToken) {
        document.getElementById('snap-container').innerHTML = `
            <div class="text-center py-8 text-red-500">
                <p>Gagal memuat halaman pembayaran</p>
                <button onclick="window.location.reload()" class="mt-4 text-indigo-600 font-medium">
                    Coba lagi
                </button>
            </div>
        `;
        return;
    }
    
    window.snap.pay(snapToken, {
        onSuccess: function(result) {
            console.log('success', result);
            window.location.href = '{{ route("payment.finish", ["order_id" => $transaction->midtrans_order_id]) }}';
        },
        onPending: function(result) {
            console.log('pending', result);
            window.location.href = '{{ route("payment.pending", ["order_id" => $transaction->midtrans_order_id]) }}';
        },
        onError: function(result) {
            console.log('error', result);
            window.location.href = '{{ route("payment.error", ["order_id" => $transaction->midtrans_order_id]) }}';
        },
        onClose: function() {
            console.log('customer closed the popup without finishing the payment');
        }
    }, '#snap-container');
}

async function checkPaymentStatus() {
    try {
        const response = await fetch('{{ route("payment.check-status", ["order_id" => $transaction->midtrans_order_id]) }}');
        const data = await response.json();
        
        if (data.paid) {
            window.location.href = '{{ route("payment.finish", ["order_id" => $transaction->midtrans_order_id]) }}';
        } else if (data.status === 'expire') {
            window.location.reload();
        }
    } catch (error) {
        console.error('Error checking payment status:', error);
    }
}
</script>
@endsection