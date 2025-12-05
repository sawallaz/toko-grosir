@extends('layouts.customer')

@section('content')

    <!-- HEADER SEARCH (Mobile – backup visual, tidak mengganggu layout utama) -->
    <div class="md:hidden mb-6 sticky top-16 z-30 bg-gray-50 pt-2 pb-1">
        <form action="{{ route('home') }}" method="GET">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="search" name="search" value="{{ request('search') }}" 
                       class="w-full bg-white border-0 rounded-2xl py-3.5 pl-12 pr-4 text-sm font-medium shadow-sm focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 transition" 
                       placeholder="Cari barang kebutuhanmu...">
            </div>
        </form>
    </div>


    <!-- BANNER (Hanya tampil di home tanpa filter) -->
    @if(!request('search') && !request('category'))
    <div class="mb-8 rounded-3xl bg-indigo-600 p-6 text-white relative overflow-hidden shadow-xl shadow-indigo-200">
        <div class="relative z-10">
            <span class="bg-white/20 backdrop-blur-md text-xs font-bold px-2 py-1 rounded uppercase tracking-wider">Promo</span>
            <h2 class="text-2xl font-black mt-2 leading-tight">Belanja Hemat<br>Harga Grosir!</h2>
            <p class="text-indigo-100 text-xs mt-2 mb-4">Dapatkan harga terbaik untuk stok warung Anda.</p>
        </div>
        <div class="absolute right-[-10px] bottom-[-20px] opacity-20 rotate-12">
            <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path></svg>
        </div>
    </div>
    @endif

    <!-- KATEGORI -->
    <div class="mb-8">
        <div class="flex justify-between items-end mb-4 px-1">
            <h3 class="font-black text-lg text-gray-800">Kategori</h3>
            @if(request('category'))
                <a href="{{ route('home') }}" class="text-xs font-bold text-red-500 hover:underline">Hapus Filter</a>
            @endif
        </div>

        <div class="flex gap-3 overflow-x-auto pb-4 no-scrollbar -mx-4 px-4">
            <a href="{{ route('home') }}" class="flex-shrink-0 px-5 py-2.5 rounded-2xl text-xs font-bold transition-all 
                {{ !request('category') ? 'bg-gray-900 text-white shadow-lg transform scale-105' : 'bg-white text-gray-600 border border-gray-100 shadow-sm hover:bg-gray-50' }}">
                Semua
            </a>

            @foreach($categories as $cat)
                <a href="{{ route('home', ['category' => $cat->id]) }}" 
                   class="flex-shrink-0 px-5 py-2.5 rounded-2xl text-xs font-bold transition-all 
                   {{ request('category') == $cat->id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 transform scale-105' : 'bg-white text-gray-600 border border-gray-100 shadow-sm hover:bg-gray-50' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>


    <!-- PRODUK GRID -->
    <div class="mb-20">
        <h3 class="font-black text-lg text-gray-800 mb-4 px-1">Paling Laris</h3>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @forelse($products as $product)

            <a href="{{ route('product.show', $product->id) }}" 
               class="group bg-white rounded-2xl p-3 shadow-sm border border-gray-100 hover:border-indigo-100 hover:shadow-lg transition-all duration-300 flex flex-col h-full">
                
                <!-- Image -->
                <div class="aspect-square bg-gray-50 rounded-xl relative overflow-hidden mb-3">
                    @if($product->foto_produk)
                        <img src="{{ Storage::url($product->foto_produk) }}" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.001M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif

                    @if($product->stock_in_base_unit == 0)
                        <div class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-10">
                            <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-1 rounded-md">STOK HABIS</span>
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="flex-1 flex flex-col">
                    <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-wide mb-1 truncate">
                        {{ $product->category->name ?? 'Umum' }}
                    </div>

                    <h4 class="font-bold text-gray-800 text-sm leading-snug mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                        {{ $product->name }}
                    </h4>

                    <div class="mt-auto pt-2 border-t border-gray-50">
                        <div class="flex items-baseline gap-0.5">
                            <span class="text-xs text-gray-400 font-medium">Rp</span>
                            <span class="text-lg font-black text-gray-900">
                                {{ number_format($product->baseUnit->price ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="text-[10px] text-gray-400">
                            per {{ $product->baseUnit->unit->name ?? 'Unit' }}
                        </div>
                    </div>
                </div>

                <div class="mt-3 bg-indigo-50 text-indigo-600 text-center py-2 rounded-lg text-xs font-bold group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    PILIH
                </div>
            </a>

            @empty
            <div class="col-span-full text-center py-16">
                <div class="bg-gray-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-600">Produk tidak ditemukan</h3>
                <p class="text-xs text-gray-400 mt-1">Coba kata kunci lain.</p>
            </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </div>

@endsection
