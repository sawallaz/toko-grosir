<!-- FORM INPUT STOK (FINAL STABIL) -->
<form :action="formAction" method="POST" @submit.prevent="submitForm">
    @csrf
    <!-- [FIX] Input Method Spoofing yang Stabil -->
    <input type="hidden" name="_method" :value="isEditMode ? 'PUT' : 'POST'">
    
    <!-- HEADER TRANSAKSI -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- No Transaksi -->
        <div class="bg-indigo-50 p-3 rounded border border-indigo-100">
            <label class="block text-xs font-bold text-indigo-500 uppercase">No. Transaksi</label>
            <div class="font-mono text-lg font-bold text-indigo-800 mt-1" x-text="isEditMode ? transactionNumber : 'STK-{{ date('ymdHis') }}'"></div>
        </div>

        <!-- Tanggal -->
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Masuk</label>
            <input type="date" name="entry_date" x-model="entryDate" 
                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                   :class="{'bg-gray-100 text-gray-500 cursor-not-allowed': isEditMode}"
                   :readonly="isEditMode" 
                   required>
        </div>

        <!-- Dibuat Oleh -->
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dibuat Oleh</label>
            <!-- [FIX] Hidden input untuk data user_id -->
            <input type="hidden" name="user_id" :value="userId">
            <select x-model="userId" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-medium bg-gray-100 text-gray-500 cursor-not-allowed" disabled>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Supplier -->
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Supplier</label>
            <div class="flex gap-2">
                <select name="supplier_id" x-model="supplierId" class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Tanpa Supplier --</option>
                    <template x-for="sup in suppliers" :key="sup.id">
                        <option :value="sup.id" x-text="sup.name"></option>
                    </template>
                </select>
                <button type="button" @click="showSupplierModal = true" class="px-3 bg-green-600 text-white rounded-md hover:bg-green-700 font-bold shadow-sm" title="Tambah Supplier">+</button>
            </div>
        </div>
    </div>

    <!-- TABEL INPUT BARANG -->
    <div class="border rounded-lg mb-6 shadow-sm bg-white relative overflow-visible">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase w-10">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase w-1/3">Cari Produk (Nama / Kode)</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase">Satuan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase w-24">Qty</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase w-40">Harga Beli (Rp)</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase w-40">Subtotal</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase w-16">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="(row, index) in rows" :key="index">
                    <tr :class="{'bg-red-50': row.error}">
                        <td class="px-4 py-2 text-center text-gray-500" x-text="index + 1"></td>
                        
                        <td class="px-4 py-2 relative">
                            <input type="text" x-model="row.product_name" @input.debounce.300ms="searchProduct(index)" @keydown.down.prevent="focusNextResult(index)" @keydown.up.prevent="focusPrevResult(index)" @keydown.enter.prevent="selectResult(index)" @keydown.right.prevent="focusNext($event)" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ketik kode/nama..." autocomplete="off">
                            <div x-show="row.showResults && row.results.length > 0" class="absolute z-[9999] w-full bg-white border border-gray-300 shadow-2xl rounded-md mt-1 max-h-60 overflow-y-auto left-0">
                                <ul>
                                    <template x-for="(res, resIdx) in row.results" :key="res.id">
                                        <li @click="selectProductManual(index, res)" class="px-4 py-3 text-sm cursor-pointer hover:bg-indigo-50 border-b flex justify-between items-center" :class="{'bg-indigo-100': row.focusIndex === resIdx}">
                                            <div>
                                                <div class="font-bold text-gray-800" x-text="res.name"></div>
                                                <div class="text-xs text-gray-500">Kode: <span x-text="res.kode_produk"></span></div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xs font-bold px-2 py-1 rounded" :class="res.stock_in_base_unit > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                                    Stok: <span x-text="res.stock_in_base_unit ?? 0"></span>
                                                </span>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </td>

                        <td class="px-4 py-2">
                            <select :name="'items['+index+'][product_unit_id]'" x-model="row.selected_unit_id" @change="updatePrice(index)" @keydown.right.prevent="focusNext($event)" @keydown.left.prevent="focusPrev($event)" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500" :disabled="!row.product_id" required>
                                <template x-for="unit in row.units" :key="unit.product_unit_id">
                                    <option :value="unit.product_unit_id" x-text="unit.name + ' (Isi: ' + unit.conversion + ')'"></option>
                                </template>
                            </select>
                        </td>

                        <!-- [PERBAIKAN BATAS INPUT QTY] -->
                        <td class="px-4 py-2">
                            <input type="number" :name="'items['+index+'][quantity]'" x-model="row.quantity" 
                                   min="1" max="999999"
                                   oninput="if(this.value.length > 6) this.value = this.value.slice(0, 6);" 
                                   placeholder="0" 
                                   @keydown.right.prevent="focusNext($event)" @keydown.left.prevent="focusPrev($event)" @keydown.enter.prevent="focusNext($event)" 
                                   class="w-full border-gray-300 rounded-md text-sm text-center focus:ring-indigo-500 focus:border-indigo-500">
                        </td>

                        <!-- [PERBAIKAN BATAS INPUT HARGA] -->
                        <td class="px-4 py-2">
                            <input type="number" :name="'items['+index+'][price]'" x-model="row.price" 
                                   placeholder="0"
                                   min="0" max="999999999999"
                                   oninput="if(this.value.length > 12) this.value = this.value.slice(0, 12);"
                                   @keydown.left.prevent="focusPrev($event)" @keydown.enter.prevent="focusNext($event)" 
                                   class="w-full border-gray-300 rounded-md text-sm text-right focus:ring-indigo-500 focus:border-indigo-500">
                        </td>

                        <td class="px-4 py-2 text-right font-mono font-bold text-gray-700">
                            <span x-text="formatRupiah((Number(row.quantity)||0) * (Number(row.price)||0))"></span>
                        </td>

                        <td class="px-4 py-2 text-center">
                            <button type="button" @click="removeRow(index)" class="text-red-500 hover:text-red-700 font-bold p-1 hover:bg-red-50 rounded">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </td>
                    </tr>
                </template>
                
                <tr><td colspan="7" class="p-2 text-center"><button type="button" @click="addRow()" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium w-full py-2 border-2 border-dashed border-gray-300 rounded-md">+ Tambah Baris Baru</button></td></tr>
            </tbody>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-gray-50 p-6 rounded-lg border border-gray-200">
        <div class="w-full md:w-1/2 mb-4 md:mb-0">
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Catatan Tambahan</label>
            <textarea name="notes" rows="2" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Cth: Barang bonus 2 pcs..."></textarea>
        </div>
        <div class="w-full md:w-1/3 text-right">
            <div class="text-sm text-gray-500 uppercase font-bold mb-1">Grand Total</div>
            <div class="text-4xl font-bold text-indigo-600 mb-4" x-text="formatRupiah(grandTotal)"></div>
            
            <div class="flex gap-2 justify-end">
                <!-- Tombol Batal & Hapus -->
                <button type="button" x-show="isEditMode" @click="deleteStockEntry()" class="px-4 py-3 bg-red-600 text-white font-bold rounded-md hover:bg-red-700 shadow-lg">HAPUS</button>
                <button type="button" x-show="isEditMode" @click="cancelEdit()" class="px-6 py-3 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 shadow-lg">BATAL</button>
                
                <!-- Tombol Simpan -->
                <button type="submit" class="px-6 py-3 text-white text-lg font-bold rounded-md shadow-lg transition-transform transform hover:scale-105"
                    :class="isEditMode ? 'bg-orange-600 hover:bg-orange-700' : 'bg-gray-900 hover:bg-gray-800'">
                    <span x-text="isEditMode ? 'UPDATE STOK' : 'SIMPAN STOK MASUK'"></span>
                </button>
            </div>
        </div>
    </div>
</form>