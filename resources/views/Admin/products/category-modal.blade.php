<div x-show="showCategoryModal"
     class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50"
     style="display: none;">
    
    <!-- [PERBAIKAN] Hapus click.away -->
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[80vh]">
        
        <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Kelola Kategori</h3>
            <button @click="showCategoryModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        
        <!-- ... (Sisa kode form & tabel biarkan sama) ... -->
        <div class="p-4 border-b bg-white">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tambah Baru</label>
            <div class="flex space-x-2">
                <input type="text" class="flex-1 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama Kategori" x-model="newCategoryName" @keydown.enter.prevent="saveNewCategory()">
                <button @click.prevent="saveNewCategory()" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700" :disabled="isSaving">
                    <span x-show="!isSaving">Simpan</span>
                    <span x-show="isSaving">...</span>
                </button>
            </div>
            <div x-show="errors.category" x-text="errors.category" class="text-xs text-red-600 mt-1"></div>
        </div>

        <div class="overflow-y-auto p-0 flex-1 bg-gray-50">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100 sticky top-0">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="category in masterCategories" :key="category.id">
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                <span x-show="editCategoryId !== category.id" x-text="category.name"></span>
                                <input x-show="editCategoryId === category.id" type="text" x-model="editCategoryName" class="w-full text-sm border-gray-300 rounded px-2 py-1">
                            </td>
                            <td class="px-4 py-2 text-right text-sm space-x-2">
                                <button x-show="editCategoryId !== category.id" @click="startEditCategory(category)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                <button x-show="editCategoryId !== category.id" @click="deleteCategory(category.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                                
                                <button x-show="editCategoryId === category.id" @click="saveEditCategory(category.id)" class="text-green-600 hover:text-green-900">OK</button>
                                <button x-show="editCategoryId === category.id" @click="cancelEditCategory()" class="text-gray-500 hover:text-gray-700">Batal</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    </div>
</div>