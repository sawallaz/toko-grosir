@extends('layouts.customer')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center p-4 text-center">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm border border-indigo-100">
        <div class="h-16 w-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-600">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h2 class="text-xl font-black text-gray-800 mb-1">Selesaikan Pembayaran</h2>
        <p class="text-sm text-gray-500 mb-6">Invoice: {{ $trx->invoice_number }}</p>
        
        <div class="text-3xl font-black text-indigo-600 mb-8">
            Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
        </div>

        <button id="pay-button" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-indigo-700 transition transform active:scale-95">
            BAYAR SEKARANG
        </button>
        
        <a href="{{ route('orders.index') }}" class="block mt-4 text-sm text-gray-400 hover:text-gray-600">Cek Status Pesanan</a>
    </div>
</div>

<script type="text/javascript">
    var payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function () {
        window.snap.pay('{{ $snapToken }}', {
            onSuccess: function(result){
                window.location.href = "{{ route('orders.index') }}";
            },
            onPending: function(result){
                window.location.href = "{{ route('orders.index') }}";
            },
            onError: function(result){
                alert("Pembayaran Gagal!");
            },
            onClose: function(){
                alert('Anda menutup popup sebelum menyelesaikan pembayaran');
            }
        });
    });
    // Auto trigger (optional)
    // payButton.click(); 
</script>
@endsection