<div x-show="showCreateModal" id="createModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
    <!-- Klik background tidak menutup modal agar aman -->
    <div class="relative w-full max-w-4xl mx-auto my-12 transition-all transform" @click.stop>
        
        <form method="POST" action="{{ route('admin.products.store') }}" class="bg-white shadow-xl sm:rounded-lg" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="form_type" value="create"> 
            
            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-semibold text-gray-900">Tambah Produk Baru</h3>
                <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">X</button>
            </div>

            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <!-- Error Handling Server Side (Backup) -->
                @if ($errors->any() && session('form_type') === 'create')
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                        <ul class="list-disc pl-5 text-sm">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                    </div>
                @endif

                <!-- Form Utama -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="name" :value="__('Nama Produk')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus @keydown.enter.prevent="focusNext($event)" x-model="form.name" />
                    </div>
                    <div>
                        <x-input-label for="kode_produk" :value="__('Kode Produk (Wajib)')" />
                        <x-text-input id="kode_produk" class="block mt-1 w-full" type="text" name="kode_produk" :value="old('kode_produk')" required @keydown.enter.prevent="focusNext($event)" x-model="form.kode_produk" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="foto_produk" :value="__('Foto (Opsional)')" />
                        <input id="foto_produk" name="foto_produk" type="file" class="mt-1 block w-full text-sm text-gray-500" @keydown.enter.prevent="focusNext($event)">
                    </div>
                    <div>
                        <x-input-label for="category_id" :value="__('Kategori (Wajib)')" />
                        <div class="flex items-center space-x-2">
                            <!-- [PERBAIKAN] Tambah 'required' agar muncul notifikasi browser -->
                            <select id="category_id" name="category_id" x-model="form.category_id" class="block w-full border-gray-300 rounded-md shadow-sm" required @keydown.enter.prevent="focusNext($event)">
                                <option value="">-- Pilih --</option>
                                <template x-for="cat in masterCategories" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name"></option>
                                </template>
                            </select>
                            <button type="button" tabindex="-1" @click="showCategoryModal = true" class="p-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600">+</button>
                        </div>
                        @error('category_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" x-model="form.status" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" @keydown.enter.prevent="focusNext($event)">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="description" :value="__('Deskripsi')" />
                        <textarea id="description" name="description" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" x-model="form.description"></textarea>
                    </div>
                </div>

                <hr class="my-6">

                <!-- Tabel Satuan (Interaktif) -->
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Satuan & Harga Jual</h4>
                    <p class="text-sm text-gray-500 mb-2">Navigasi: Gunakan panah Kiri/Kanan/Atas/Bawah atau Enter.</p>

                    <input type="hidden" name="is_base_unit_index" :value="baseRowIndex">

                    <template x-for="(unit, index) in productRows" :key="index">
                        <div class="grid grid-cols-12 gap-4 p-4 mb-2 border rounded-lg items-start">
                            <div class="col-span-12 md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700">Satuan</label>
                                <div class="flex items-center space-x-2">
                                    <select :name="'units['+index+'][unit_id]'" x-model="unit.unit_id" @change="refreshUnitNames()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required 
                                            @keydown.enter.prevent="focusNext($event)"
                                            @keydown.right.prevent="focusGrid($event, index, 'price')"
                                            @keydown.down.prevent="focusGrid($event, index + 1, 'unit_id')"
                                            @keydown.up.prevent="focusGrid($event, index - 1, 'unit_id')">
                                        <option value="">-- Pilih --</option>
                                        <template x-for="u in masterUnits" :key="u.id">
                                            <option :value="u.id" :data-name="u.short_name" x-text="u.name + ' (' + u.short_name + ')'"></option>
                                        </template>
                                    </select>
                                    <button type="button" tabindex="-1" @click="showUnitModal = true" class="p-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600 mt-1">+</button>
                                </div>
                            </div>
                            <div class="col-span-12 md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Harga Jual</label>
                                <input type="number" :name="'units['+index+'][price]'" x-model="unit.price" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="0" required
                                       @keydown.enter.prevent="focusNext($event)"
                                       @keydown.left.prevent="focusGrid($event, index, 'unit_id')"
                                       @keydown.right.prevent="focusGrid($event, index, 'conversion')"
                                       @keydown.down.prevent="focusGrid($event, index + 1, 'price')"
                                       @keydown.up.prevent="focusGrid($event, index - 1, 'price')">
                            </div>
                            <div class="col-span-12 md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Konversi</label>
                                <input type="number" :name="'units['+index+'][conversion]'" x-model="unit.conversion" :readonly="unit.is_base_unit" :class="{'bg-gray-100 text-gray-500 cursor-not-allowed': unit.is_base_unit}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="1"
                                       @keydown.enter.prevent="focusNext($event)"
                                       @keydown.left.prevent="focusGrid($event, index, 'price')"
                                       @keydown.down.prevent="focusGrid($event, index + 1, 'conversion')"
                                       @keydown.up.prevent="focusGrid($event, index - 1, 'conversion')">
                                <p class="text-xs text-gray-500" x-show="!unit.is_base_unit">... <span x-text="baseUnitName"></span></p>
                                <p class="text-xs text-green-600 font-bold" x-show="unit.is_base_unit">Satuan Dasar</p>
                            </div>
                            <div class="col-span-12 md:col-span-3 flex items-center space-x-2 pt-7">
                                <button type="button" @click="setBaseUnit(index)" :class="unit.is_base_unit ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border'" class="px-3 py-2 text-xs rounded-md">Dasar</button>
                                <button type="button" @click="removeUnit(index)" class="px-3 py-2 text-xs rounded-md bg-red-600 text-white">Hapus</button>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addUnit()" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 hover:bg-gray-300">
                        + Tambah Satuan Lain
                    </button>
                </div>
            </div>

            <div class="flex justify-end p-6 border-t bg-gray-50 sm:rounded-b-lg">
                <button type="button" @click="showCreateModal = false" class="mr-4 px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase hover:bg-indigo-700">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>