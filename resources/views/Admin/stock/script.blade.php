<script>
    function stockPageManager() {
        return {
            suppliers: @json($suppliers),
            supplierId: '',
            showSupplierModal: false,
            newSupplierName: '', newSupplierPhone: '',
            
            entryDate: '{{ date('Y-m-d') }}',
            transactionNumber: '',
            userId: '{{ Auth::id() }}',
            rows: [],
            
            isEditMode: false,
            editId: null,
            formAction: '{{ route('admin.stok.store') }}',
            expandedRows: [],

            init() {
                this.addRow();
                const urlParams = new URLSearchParams(window.location.search);
                if(urlParams.get('tab')) this.tab = urlParams.get('tab');
            },

            // --- EDIT LOGIC ---
            async editStockEntry(id) {
                try {
                    const res = await fetch(`{{ url('admin/stok') }}/${id}/edit-json`);
                    if(!res.ok) throw new Error('Gagal fetch');
                    const data = await res.json();

                    this.isEditMode = true;
                    this.editId = id;
                    this.formAction = `{{ url('admin/stok') }}/${id}`;
                    
                    this.entryDate = data.entry_date;
                    this.supplierId = data.supplier_id ? data.supplier_id : ''; 
                    this.userId = data.user_id;
                    this.transactionNumber = data.transaction_number;

                    this.rows = data.details.map(detail => {
                        let product = detail.product_unit.product;
                        return {
                            product_name: product.name,
                            product_id: product.id,
                            units: product.units.map(u => ({
                                product_unit_id: u.id,
                                name: u.unit ? u.unit.name : 'Unit',
                                conversion: u.conversion_to_base,
                                harga_beli_modal: u.harga_beli_modal
                            })),
                            selected_unit_id: detail.product_unit_id,
                            quantity: detail.quantity,
                            price: detail.price_at_entry,
                            results: [], showResults: false, focusIndex: -1, error: false
                        };
                    });
                    
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                } catch (e) {
                    alert('Gagal memuat data edit.');
                    console.error(e);
                }
            },

            cancelEdit() {
                this.isEditMode = false;
                this.editId = null;
                this.formAction = '{{ route('admin.stok.store') }}';
                this.entryDate = '{{ date('Y-m-d') }}';
                this.supplierId = '';
                this.rows = [];
                this.addRow();
            },

            // --- MANAJEMEN BARIS ---
            addRow() {
                this.rows.push({
                    product_name: '', product_id: null, units: [], selected_unit_id: null, 
                    quantity: '', price: '', results: [], showResults: false, focusIndex: -1, error: false
                });
            },
            
            removeRow(index) {
                if(this.rows.length > 1) {
                    this.rows.splice(index, 1);
                } else {
                    this.rows[0] = { product_name: '', product_id: null, units: [], selected_unit_id: null, quantity: '', price: '', results: [], showResults: false, focusIndex: -1, error: false };
                }
            },

            async searchProduct(index) {
                let query = this.rows[index].product_name;
                if (query.length < 1) { this.rows[index].results = []; this.rows[index].showResults = false; return; }
                try {
                    const res = await fetch(`{{ route('admin.stok.searchProduct') }}?q=${query}`);
                    const data = await res.json();
                    this.rows[index].results = data;
                    this.rows[index].showResults = true;
                } catch (e) {}
            },

            selectProductManual(index, product) {
                let row = this.rows[index];
                row.product_id = product.id;
                row.product_name = product.name; 
                row.units = product.units;       
                row.results = [];
                row.showResults = false;
                
                let baseUnit = product.units.find(u => u.is_base) || product.units[0];
                if (baseUnit) {
                    row.selected_unit_id = baseUnit.product_unit_id;
                    row.price = Number(baseUnit.harga_beli_modal) > 0 ? Number(baseUnit.harga_beli_modal) : ''; 
                }
                if(row.quantity === '') row.quantity = 1;

                this.$nextTick(() => {
                     const inputs = document.querySelectorAll(`input[name='items[${index}][quantity]']`);
                     if(inputs.length) inputs[0].focus();
                });

                if (index === this.rows.length - 1) this.addRow();
            },

            updatePrice(index) {
                let row = this.rows[index];
                let unit = row.units.find(u => u.product_unit_id == row.selected_unit_id);
                if (unit) row.price = Number(unit.harga_beli_modal) > 0 ? Number(unit.harga_beli_modal) : ''; 
            },

            // --- NAVIGASI KEYBOARD ---
            focusNext(e) { const inputs = Array.from(document.querySelectorAll('input:not([type="hidden"]), select, textarea, button[type="submit"]')).filter(el => !el.disabled && el.offsetParent !== null); const index = inputs.indexOf(e.target); if (index > -1 && index < inputs.length - 1) { inputs[index + 1].focus(); if(inputs[index + 1].tagName === 'INPUT') inputs[index + 1].select(); } },
            focusPrev(e) { const inputs = Array.from(document.querySelectorAll('input:not([type="hidden"]), select, textarea, button[type="submit"]')).filter(el => !el.disabled && el.offsetParent !== null); const index = inputs.indexOf(e.target); if (index > 0) { inputs[index - 1].focus(); if(inputs[index - 1].tagName === 'INPUT') inputs[index - 1].select(); } },
            focusNextResult(index) { let row = this.rows[index]; if (row.focusIndex < row.results.length - 1) row.focusIndex++; },
            focusPrevResult(index) { let row = this.rows[index]; if (row.focusIndex > 0) row.focusIndex--; },
            selectResult(index) { let row = this.rows[index]; if (row.focusIndex >= 0 && row.results[row.focusIndex]) { this.selectProductManual(index, row.results[row.focusIndex]); } },

            async saveSupplier() { try { const res = await fetch('{{ route('admin.stok.storeSupplierAjax') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ name: this.newSupplierName, phone: this.newSupplierPhone }) }); const data = await res.json(); this.suppliers.push(data); this.supplierId = data.id; this.showSupplierModal = false; } catch (e) {} },
            async deleteStockEntry() { if (!confirm('Hapus transaksi ini?')) return; let form = document.createElement('form'); form.method = 'POST'; form.action = `{{ url('admin/stok') }}/${this.editId}`; let csrf = document.createElement('input'); csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}'; form.appendChild(csrf); let method = document.createElement('input'); method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE'; form.appendChild(method); document.body.appendChild(form); form.submit(); },
            
            toggleRow(id) { if (this.expandedRows.includes(id)) this.expandedRows = this.expandedRows.filter(i => i !== id); else this.expandedRows.push(id); },
            isExpanded(id) { return this.expandedRows.includes(id); },
            get grandTotal() { return this.rows.reduce((total, row) => total + ((Number(row.quantity)||0) * (Number(row.price)||0)), 0); },
            formatRupiah(number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number); },
            
            // [PERBAIKAN UTAMA: SUBMIT FORM]
            submitForm(e) {
                // 1. Filter: Hapus semua baris yang tidak ada produknya (termasuk baris kosong terakhir)
                this.rows = this.rows.filter(row => row.product_id !== null && row.product_id !== '');

                // 2. Validasi minimal 1 baris
                if(this.rows.length === 0) {
                    alert('Mohon pilih minimal satu produk.');
                    this.addRow(); // Kembalikan 1 baris kosong biar tidak jelek
                    return;
                }

                // 3. [GACOR] Tunggu Alpine update DOM (menghapus baris kosong dari layar)
                // Baru setelah itu kita submit. Ini kuncinya!
                this.$nextTick(() => {
                    // Cek Validasi Browser (Required field dll)
                    if (!e.target.checkValidity()) {
                        e.target.reportValidity();
                        return;
                    }
                    // Kirim
                    e.target.submit();
                });
            }
        }
    }
</script>