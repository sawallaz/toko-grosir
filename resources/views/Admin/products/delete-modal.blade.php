<!-- =================================================================== -->
<!-- [BARU] MODAL "KONFIRMASI HAPUS PRODUK"                            -->
<!-- =================================================================== -->
<div x-show="showDeleteModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
     @click.away="showDeleteModal = false" x-transition style="display: none;">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md" @click.stop>
        <!-- Form Hapus -->
        <form :action="deleteFormAction" method="POST">
            @csrf
            @method('DELETE')
            <h3 class="text-lg font-medium text-gray-900 mb-2">Konfirmasi Hapus</h3>
            <p class="text-sm text-gray-600 mb-4">
                Anda yakin ingin menghapus produk <strong x-text="deleteProductName"></strong>? Stok dan data satuan terkait akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex justify-end space-x-4">
                <x-secondary-button type="button" @click.prevent="showDeleteModal = false">Batal</x-secondary-button>
                <x-danger-button type="submit">Ya, Hapus</x-danger-button>
            </div>
        </form>
    </div>
</div>