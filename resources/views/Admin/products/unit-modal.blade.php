<div x-show="showUnitModal"
     class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50"
     @click.away="showUnitModal = false" x-transition style="display: none;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[80vh]">
        
        <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Kelola Satuan</h3>
            <button @click="showUnitModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>

        <div class="p-4 border-b bg-white">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tambah Baru</label>
            <div class="flex space-x-2">
                <div class="flex-1">
                    <input type="text" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nama (cth: Kardus)" x-model="newUnitName">
                </div>
                <div class="w-24">
                    <input type="text" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Singkat (dos)" x-model="newUnitShortName" @keydown.enter.prevent="saveNewUnit()">
                </div>
                <button @click.prevent="saveNewUnit()" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700" :disabled="isSaving">
                    <span x-show="!isSaving">Simpan</span>
                    <span x-show="isSaving">...</span>
                </button>
            </div>
            <div x-show="errors.unit" x-text="errors.unit" class="text-xs text-red-600 mt-1"></div>
        </div>

        <div class="overflow-y-auto p-0 flex-1 bg-gray-50">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100 sticky top-0">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Singkatan</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="unit in masterUnits" :key="unit.id">
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                <span x-show="editUnitId !== unit.id" x-text="unit.name"></span>
                                <input x-show="editUnitId === unit.id" type="text" x-model="editUnitName" class="w-full text-sm border-gray-300 rounded px-2 py-1">
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">
                                <span x-show="editUnitId !== unit.id" x-text="unit.short_name"></span>
                                <input x-show="editUnitId === unit.id" type="text" x-model="editUnitShortName" class="w-full text-sm border-gray-300 rounded px-2 py-1">
                            </td>
                            <td class="px-4 py-2 text-right text-sm space-x-2">
                                <button x-show="editUnitId !== unit.id" @click="startEditUnit(unit)" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                <button x-show="editUnitId !== unit.id" @click="deleteUnit(unit.id)" class="text-red-600 hover:text-red-900">Hapus</button>
                                
                                <button x-show="editUnitId === unit.id" @click="saveEditUnit(unit.id)" class="text-green-600 hover:text-green-900">OK</button>
                                <button x-show="editUnitId === unit.id" @click="cancelEditUnit()" class="text-gray-500 hover:text-gray-700">Batal</button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="masterUnits.length === 0">
                        <td colspan="3" class="px-4 py-4 text-center text-xs text-gray-500">Belum ada satuan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>