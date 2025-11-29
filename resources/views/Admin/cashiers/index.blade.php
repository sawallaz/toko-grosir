@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Manajemen Akun Kasir') }}
    </h2>
@endsection

@section('content')
<div class="container mx-auto" x-data="cashierManager()">

    <!-- Header dengan Pencarian -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div class="text-gray-600 text-sm">
            Kelola akun staff kasir toko Anda.
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <!-- Form Pencarian -->
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    @input.debounce.500ms="performSearch()"
                    placeholder="Cari nama, email, atau telepon..." 
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-full md:w-64 text-sm"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Tombol Tambah -->
            <button @click="openModal('create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center justify-center shadow-lg transition-all duration-200 hover:scale-105 whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kasir
            </button>
        </div>
    </div>

    <!-- Notifikasi -->
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabel Kasir -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Avatar</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama & Email</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Telepon</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($cashiers as $kasir)
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center justify-center">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center overflow-hidden border-2 border-white shadow-sm">
                                @if($kasir->avatar)
                                    <img src="{{ Storage::url($kasir->avatar) }}" alt="{{ $kasir->name }}" class="h-full w-full object-cover">
                                @else
                                    <span class="text-indigo-600 font-bold text-sm">{{ substr($kasir->name, 0, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-gray-900">{{ $kasir->name }}</div>
                        <div class="text-xs text-gray-500 font-mono mt-1">{{ $kasir->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $kasir->phone ?: '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <button 
                            onclick="toggleStatus({{ $kasir->id }}, '{{ $kasir->status }}')" 
                            class="px-3 py-2 text-xs font-bold rounded-full transition-colors cursor-pointer border {{ $kasir->status === 'active' ? 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' : 'bg-red-100 text-red-800 border-red-200 hover:bg-red-200' }}"
                        >
                            {{ $kasir->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex items-center justify-center space-x-2">
                            <button onclick="editKasir({{ $kasir->id }})" class="text-indigo-600 hover:text-indigo-900 font-bold bg-indigo-50 px-3 py-2 rounded-lg hover:bg-indigo-100 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                            <form action="{{ route('admin.cashiers.destroy', $kasir->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun kasir {{ $kasir->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 font-bold bg-red-50 px-3 py-2 rounded-lg hover:bg-red-100 transition-colors duration-200 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            <span class="text-lg font-medium text-gray-400">Belum ada akun kasir</span>
                            <p class="text-sm text-gray-500 mt-2">Klik tombol "Tambah Kasir" untuk membuat akun baru</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $cashiers->links() }}
        </div>
    </div>

    <!-- MODAL CREATE / EDIT -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden mx-4" @click.away="showModal = false">
            <form :action="formAction" method="POST" enctype="multipart/form-data">
                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Header Modal -->
                <div class="px-6 py-4 border-b bg-gradient-to-r from-indigo-500 to-purple-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white" x-text="mode === 'create' ? 'Tambah Kasir Baru' : 'Edit Data Kasir'"></h3>
                    <button type="button" @click="showModal = false" class="text-white hover:text-gray-200 text-xl font-bold">&times;</button>
                </div>
                
                <!-- Body Modal -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    @if ($errors->any())
                        <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error) 
                                    <li>{{ $error }}</li> 
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Avatar Upload -->
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                        <div class="h-20 w-20 rounded-full bg-white border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden shadow-sm">
                            <img x-show="avatarPreview" :src="avatarPreview" class="h-full w-full object-cover">
                            <svg x-show="!avatarPreview" class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Foto Profil</label>
                            <input type="file" name="avatar" @change="previewImage" accept="image/jpeg,image/png,image/jpg" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-white file:text-indigo-600 hover:file:bg-gray-50">
                            <p class="text-xs text-gray-500 mt-1">JPEG, PNG, JPG - Maks. 2MB</p>
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="name" x-model="form.name" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" x-model="form.email" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Telepon</label>
                            <input type="text" name="phone" x-model="form.phone" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" placeholder="Opsional">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2" x-text="mode === 'create' ? 'Password' : 'Password Baru'"></label>
                                <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" :required="mode === 'create'" placeholder="••••••••">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi</label>
                                <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" :required="mode === 'create'" placeholder="••••••••">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Status Akun</label>
                            <select name="status" x-model="form.status" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                <option value="active">🟢 Aktif - Bisa login</option>
                                <option value="inactive">🔴 Nonaktif - Tidak bisa login</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-6 py-3 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700 transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="mode === 'create' ? 'Simpan' : 'Update'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
async function toggleStatus(id, currentStatus) {
    try {
        const url = `/admin/cashiers/${id}/toggle-status`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                // Tidak perlu _method untuk POST biasa
            })
        });

        const result = await response.json();

        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Gagal mengubah status');
    }
}


async function editKasir(id) {
    console.log('Edit kasir dipanggil:', id);
    
    try {
        const res = await fetch(`/admin/cashiers/${id}/edit-json`);
        const data = await res.json();
        
        console.log('Data diterima:', data);
        
        // CARA YANG BENAR UNTUK AKSES ALPINE COMPONENT
        const alpineElement = document.querySelector('[x-data="cashierManager()"]');
        if (!alpineElement) {
            throw new Error('Alpine component tidak ditemukan');
        }
        
        // Gunakan Alpine reactive state
        Alpine.$data(alpineElement).form.name = data.name;
        Alpine.$data(alpineElement).form.email = data.email;
        Alpine.$data(alpineElement).form.phone = data.phone || '';
        Alpine.$data(alpineElement).form.status = data.status;
        Alpine.$data(alpineElement).avatarPreview = data.avatar ? `/storage/${data.avatar}` : null;
        Alpine.$data(alpineElement).formAction = `/admin/cashiers/${id}`;
        Alpine.$data(alpineElement).mode = 'edit';
        Alpine.$data(alpineElement).showModal = true;
        
        console.log('Modal seharusnya terbuka sekarang');
        
    } catch(e) { 
        console.error('Error:', e);
        alert('Gagal load data: ' + e.message);
    }
}

// Fungsi pencarian
function performSearch() {
    const searchQuery = document.querySelector('[x-model="searchQuery"]').value;
    
    if (searchQuery.length === 0 || searchQuery.length >= 2) {
        const url = new URL(window.location.href);
        if (searchQuery) {
            url.searchParams.set('search', searchQuery);
        } else {
            url.searchParams.delete('search');
        }
        window.location.href = url.toString();
    }
}


function cashierManager() {
    return {
        showModal: {{ $errors->any() ? 'true' : 'false' }},
        mode: 'create',
        formAction: '{{ route('admin.cashiers.store') }}',
        avatarPreview: null,
        searchQuery: '{{ request('search', '') }}',
        form: { 
            name: '{{ old('name', '') }}', 
            email: '{{ old('email', '') }}', 
            phone: '{{ old('phone', '') }}', 
            status: '{{ old('status', 'active') }}' 
        },

        init() {
            if ({{ $errors->any() ? 'true' : 'false' }}) {
                this.mode = '{{ old('_method') === 'PUT' ? 'edit' : 'create' }}';
            }
        },

        openModal(mode) {
            this.mode = mode;
            this.showModal = true;
            this.avatarPreview = null;
            if(mode === 'create') {
                this.form = { name: '', email: '', phone: '', status: 'active' };
                this.formAction = "{{ route('admin.cashiers.store') }}";
            }
        },

        previewImage(e) {
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = (e) => { 
                    this.avatarPreview = e.target.result; 
                }
                reader.readAsDataURL(file);
            }
        }
    }
}



document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('[x-model="searchQuery"]');
    if (searchInput) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');
        if (searchParam) {
            searchInput.value = searchParam;
        }
    }
});
</script>
@endsection