<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posSystem', () => ({
        // STATE AWAL
        tab: 'sales',
        invoiceNumber: 'INV-' + Date.now().toString().slice(-8),

        // Online Order
            showOnlineDetailModal: false,
            onlineOrder: null,
            isProcessingOnline: false,

        
        // WAKTU REAL-TIME
        currentDate: '',
        currentTime: '',
        timeInterval: null,
        
        // DATA TRANSAKSI
        cart: [{
            tempId: Date.now(), 
            product_name: '', 
            product_id: null, 
            code: '', 
            product_unit_id: null, 
            unit_name: '', 
            unit_short_name: '', 
            price: 0, 
            qty: 1, 
            subtotal: 0, 
            available_units: [], 
            isWholesale: false, 
            results: [], 
            showResults: false, 
            focusIndex: -1, 
            error: false,
            active: false
        }],
        
        // CUSTOMER - DITAMBAH FITUR CRUD
        customerSearchInput: '', 
        selectedCustomer: null, 
        customerFeedback: '', 
        customerFeedbackColor: '',
        showCustomerModal: false, 
        customersList: [], // List semua customer untuk modal
        newCustomerName: '', 
        newCustomerPhone: '',
        editingCustId: null, 
        editCustName: '', 
        editCustPhone: '',

        // PAYMENT
        payAmount: '', 
        isProcessing: false,
        
        // HISTORY
        historyData: [], 
        historyFilter: { q: '', user_id: '', start_date: '', end_date: '' },

        init() {
            console.log('POS System Initialized');
            
            // Initialize waktu real-time
            this.updateTime();
            this.timeInterval = setInterval(() => this.updateTime(), 1000);
            
            // Focus ke input pertama saat load
            this.$nextTick(() => {
                this.focusFirstInput();
            });
            
            // Keyboard shortcuts
            this.setupKeyboardShortcuts();
            
            // Load history jika di tab history
            if (this.tab === 'history') {
                this.loadHistory();
            }
        },

         focusFirstInput() {
            this.$nextTick(() => {
                const firstInput = document.querySelector('input[placeholder="Scan / Ketik..."]');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        },

        // Helper function untuk get CSRF token
        getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                   document.querySelector('input[name="_token"]')?.value ||
                   '{{ csrf_token() }}';
        },

        // Waktu real-time
        updateTime() {
            const now = new Date();
            this.currentDate = now.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            this.currentTime = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
        },

        // SWITCH TAB dengan auto load history
        switchTab(tabName) {
            this.tab = tabName;
            if (tabName === 'history') {
                this.$nextTick(() => {
                    this.loadHistory();
                });
            }
            if (tabName === 'sales') {
                this.$nextTick(() => {
                    this.focusFirstInput();
                });
            }
        },

        // KEYBOARD SHORTCUTS
setupKeyboardShortcuts() {
    let enterPressed = false; // Flag untuk prevent multiple Enter
    
    document.addEventListener('keydown', (e) => {
        // F1 - Tab Sales
        if(e.key === 'F1') { 
            e.preventDefault(); 
            this.switchTab('sales');
        }
        // F2 - Tab History
        if(e.key === 'F2') { 
            e.preventDefault(); 
            this.switchTab('history');
        }

        // ESC - Reset customer search atau close modal
        if(e.key === 'Escape') {
            if(this.showCustomerModal) {
                this.showCustomerModal = false;
            } else if(this.selectedCustomer) {
                this.resetCustomer();
            }
        }
        
        // Enter untuk navigasi HANYA di dalam tabel
        if(e.key === 'Enter' && this.tab === 'sales') {
            const activeElement = document.activeElement;
            const isInTable = activeElement.closest('tbody');
            
            if (isInTable && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'SELECT')) {
                e.preventDefault();
                this.focusNext(e);
            }
            
            // Enter di input payAmount - PREVENT MULTIPLE
            if (activeElement === document.querySelector('input[x-model="payAmount"]') && !enterPressed) {
                e.preventDefault();
                enterPressed = true; // Set flag
                
                setTimeout(() => {
                    enterPressed = false; // Reset flag setelah 1 detik
                }, 1000);
                
                this.processPayment();
            }
        }
    });
},


        // --- NAVIGASI ENTER ---
                
        focusNext(e) {
            // Hanya proses navigasi untuk input di dalam tabel
            const isInTable = e.target.closest('tbody');
            if (!isInTable) {
                return; // Jika bukan di tabel, biarkan default behavior
            }
            
            // Cari semua input/select hanya di dalam tabel
            const tableInputs = Array.from(document.querySelectorAll('tbody input:not([type="hidden"]), tbody select'))
                                    .filter(el => !el.disabled && el.offsetParent !== null);
            
            const index = tableInputs.indexOf(e.target);
            
            if (index === tableInputs.length - 1) {
                // Input terakhir di tabel, tambah baris baru
                let lastRow = this.cart[this.cart.length - 1];
                if(lastRow.product_id) {
                    this.addRow();
                }
            } else if (index > -1) {
                tableInputs[index + 1].focus();
                if(tableInputs[index + 1].tagName === 'INPUT') tableInputs[index + 1].select();
            }
        },

        focusPrev(e) {
            const isInTable = e.target.closest('tbody');
            if (!isInTable) return;
            
            const tableInputs = Array.from(document.querySelectorAll('tbody input:not([type="hidden"]), tbody select'))
                                    .filter(el => !el.disabled && el.offsetParent !== null);
            
            const index = tableInputs.indexOf(e.target);
            if (index > 0) {
                tableInputs[index - 1].focus();
                if(tableInputs[index - 1].tagName === 'INPUT') tableInputs[index - 1].select();
            }
        },

        // --- PRODUCT SEARCH & SELECTION ---
        async searchProduct(index) {
            let row = this.cart[index];
            let query = row.product_name.trim();
            
            if (query.length < 1) { 
                row.results = []; 
                row.showResults = false; 
                return; 
            }
            
            try {
                const response = await fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(query)}`);
                if (!response.ok) throw new Error('Network response was not ok');
                
                row.results = await response.json();
                row.showResults = true; 
                row.focusIndex = -1;
            } catch (error) {
                console.error('Search error:', error);
                row.results = [];
                row.showResults = false;
            }
        },

        selectProductManual(index, product) {
            let row = this.cart[index];
            row.product_id = product.id;
            row.product_name = product.name;
            row.code = product.kode_produk;
            row.available_units = product.units || [];
            row.results = []; 
            row.showResults = false;
            row.error = false;

            // Auto select unit (prioritaskan base unit)
            if (row.available_units.length > 0) {
                let unit = row.available_units.find(u => u.is_base) || row.available_units[0];
                if(unit) {
                    row.product_unit_id = unit.product_unit_id;
                    row.unit_name = unit.unit_name;
                    row.unit_short_name = unit.unit_short_name;
                    row.price = Number(unit.price);
                    this.updatePrice(index);
                }
            }

            // Auto-focus ke quantity
            this.$nextTick(() => {
                const inputs = document.querySelectorAll(`input[x-model="row.qty"]`);
                if(inputs[index]) {
                    inputs[index].focus();
                    inputs[index].select();
                }
            });
        },
        
        // Keyboard navigation in search results
        focusNextResult(index) { 
            let row = this.cart[index]; 
            if (row.focusIndex < row.results.length - 1) {
                row.focusIndex++; 
            }
        },
        
        focusPrevResult(index) { 
            let row = this.cart[index]; 
            if (row.focusIndex > 0) {
                row.focusIndex--; 
            }
        },
        
        selectResult(index) { 
            let row = this.cart[index]; 
            if (row.focusIndex >= 0 && row.results[row.focusIndex]) {
                this.selectProductManual(index, row.results[row.focusIndex]); 
            }
        },

        // --- ROW MANAGEMENT ---
        addRow() {
    const newRow = {
        tempId: Date.now(), 
        product_name: '', 
        product_id: null, 
        code: '', 
        product_unit_id: null, 
        unit_name: '', 
        unit_short_name: '',
        price: 0, 
        qty: 1, 
        subtotal: 0, 
        available_units: [], 
        isWholesale: false, 
        results: [], 
        showResults: false, 
        focusIndex: -1, 
        error: false,
        active: false
    };
    
    this.cart.push(newRow);
    
    
    this.$nextTick(() => {
        // Focus ke input product di baris baru
        const newInputs = document.querySelectorAll('input[placeholder="Scan / Ketik..."]');
        if(newInputs.length > 0) {
            const lastInput = newInputs[newInputs.length - 1];
            lastInput.focus();
        }
    });
},
        
        removeRow(index) {
            if (this.cart.length > 1) {
                this.cart.splice(index, 1);
            } else {
                // Reset baris pertama jika hanya ada satu baris
                this.cart[0] = { 
                    tempId: Date.now(), 
                    product_name: '', 
                    product_id: null, 
                    code: '', 
                    product_unit_id: null, 
                    unit_name: '', 
                    unit_short_name: '',
                    price: 0, 
                    qty: 1, 
                    subtotal: 0, 
                    available_units: [], 
                    isWholesale: false, 
                    results: [], 
                    showResults: false, 
                    focusIndex: -1, 
                    error: false,
                    active: false
                };
            }
            
            this.$nextTick(() => {
                this.focusFirstInput();
            });
        },

        // --- PRICE & CALCULATIONS ---
        updateUnit(index) {
            let row = this.cart[index];
            if (!row.available_units || row.available_units.length === 0) return;
            
            let unit = row.available_units.find(u => u.product_unit_id == row.product_unit_id);
            if(unit) { 
                row.price = Number(unit.price);
                row.unit_short_name = unit.unit_short_name;
                this.updatePrice(index); 
            }
        },
        
        updateSubtotal(index) {
            this.updatePrice(index);
        },
        
        updatePrice(index) {
            let row = this.cart[index];
            
            // Reset jika tidak ada product
            if (!row.product_id) {
                row.price = 0;
                row.subtotal = 0;
                row.isWholesale = false;
                return;
            }
            
            // Cari data unit
            let unitData = row.available_units.find(u => u.product_unit_id == row.product_unit_id);
            if (!unitData) {
                row.subtotal = 0;
                return;
            }
            
            // Cek harga grosir
            if(unitData.wholesale && unitData.wholesale.length > 0) {
                let rules = unitData.wholesale.sort((a, b) => b.min - a.min);
                let appliedRule = rules.find(r => row.qty >= r.min);
                if(appliedRule) { 
                    row.price = Number(appliedRule.price); 
                    row.isWholesale = true; 
                } else { 
                    row.price = Number(unitData.price); 
                    row.isWholesale = false; 
                }
            } else {
                row.price = Number(unitData.price);
                row.isWholesale = false;
            }
            
            // Hitung subtotal
            row.subtotal = (row.qty || 0) * row.price;
        },

        // --- RESET CART & NEW INVOICE ---
        resetCart(force = false) { 
        if(force || confirm('Reset transaksi?')) { 
            this.cart = []; 
            this.addRow(); 
            this.payAmount = ''; 
            this.selectedCustomer = null; 
            this.customerSearchInput = ''; 
            this.customerFeedback = '';
            // HAPUS generate invoice di sini, biarkan kosong
            this.invoiceNumber = '';
        } 
    },

        // GETTERS
        get grandTotal() { 
            return this.cart.reduce((total, item) => total + (item.subtotal || 0), 0); 
        },
        
        get changeAmount() { 
            let paid = Number(this.payAmount) || 0;
            return paid >= this.grandTotal ? paid - this.grandTotal : -1;
        },

        // --- PAYMENT PROCESSING ---
        async processPayment() {
        // PREVENT DOUBLE SUBMISSION
        if (this.isProcessing) {
            console.log('Transaction already in progress...');
            return;
        }
        
        // Validasi
        let validCart = this.cart.filter(row => row.product_id && row.qty > 0);
        if (validCart.length === 0) {
            alert('Keranjang kosong! Tambahkan produk terlebih dahulu.');
            return;
        }
        
        if (Number(this.payAmount) < this.grandTotal) {
            alert('Uang Kurang!');
            return;
        }
        
        // SET PROCESSING STATE
        this.isProcessing = true;
        
        // DISABLE tombol Bayar secara visual
        const payButton = document.querySelector('button[x-show="!isProcessing"]');
        if (payButton) {
            payButton.disabled = true;
        }
        
        try {
            const payload = {
                cart: validCart.map(item => ({
                    product_unit_id: item.product_unit_id,
                    qty: item.qty,
                    price: item.price
                })),
                total_amount: this.grandTotal,
                pay_amount: this.payAmount,
                customer_id: this.selectedCustomer?.id || null,
                payment_method: 'cash'
            };
            
            console.log('Sending transaction...', payload);
            
            const response = await fetch('{{ route("pos.store") }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            console.log('Transaction response:', data);
            
            if(data.status === 'success') {
                // Cetak struk
                this.printReceipt(data.invoice_number);
                
                // Tampilkan info kembalian
                if(this.changeAmount >= 0) {
                    alert('Transaksi berhasil!\nKembalian: ' + this.formatRupiah(this.changeAmount));
                }
                
                // Reset transaksi
                this.resetCart(true);
                
                // Refresh history
                this.loadHistory();
                
            } else {
                alert(data.message || 'Terjadi kesalahan');
            }
        } catch(error) {
            console.error('Payment error:', error);
            alert('Error sistem: ' + error.message);
        } finally {
            // RESET PROCESSING STATE - PASTIKAN INI DIJALANKAN
            this.isProcessing = false;
            
            // ENABLE tombol Bayar
            const payButton = document.querySelector('button[x-show="!isProcessing"]');
            if (payButton) {
                payButton.disabled = false;
            }
        }
    },

        // --- CUSTOMER MANAGEMENT (UPDATE DENGAN FITUR CRUD) ---
        async findCustomer() {
            if (!this.customerSearchInput.trim()) {
                this.customerFeedback = 'Masukkan nomor HP atau nama';
                this.customerFeedbackColor = 'text-yellow-600';
                return;
            }
            
            try {
                const response = await fetch(`{{ route('pos.customer.search') }}?q=${encodeURIComponent(this.customerSearchInput)}`);
                const data = await response.json();
                
                if(data.length > 0) {
                    this.selectedCustomer = data[0];
                    this.customerFeedback = 'OK';
                    this.customerFeedbackColor = 'text-green-600';
                } else {
                    this.selectedCustomer = null;
                    this.customerFeedback = 'Nihil';
                    this.customerFeedbackColor = 'text-red-500';
                }
            } catch(error) {
                this.customerFeedback = 'Error pencarian';
                this.customerFeedbackColor = 'text-red-500';
            }
        },

        // --- MANAJEMEN CUSTOMER (BARU) ---
        openCustomerModal() {
            this.showCustomerModal = true;
            this.loadCustomers();
        },
        
        async loadCustomers() {
            try {
                let res = await fetch('{{ route("pos.customer.list") }}');
                this.customersList = await res.json();
            } catch(e) {
                console.error('Error loading customers:', e);
                alert('Gagal memuat data member');
            }
        },

        async saveCustomer() {
            if(!this.newCustomerName.trim()) {
                alert('Nama wajib diisi');
                return;
            }

            // Validasi nomor HP harus angka
            if(this.newCustomerPhone && !/^\d+$/.test(this.newCustomerPhone)) {
                alert('Nomor HP harus berupa angka');
                return;
            }
            
            // Validasi panjang nomor HP
            if(this.newCustomerPhone && this.newCustomerPhone.length < 10) {
                alert('Nomor HP minimal 10 digit');
                return;
            }
            
            try {
                let res = await fetch('{{ route("pos.customer.storeAjax") }}', {
                    method: 'POST', 
                    headers: {
                        'Content-Type':'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        name: this.newCustomerName, 
                        phone: this.newCustomerPhone 
                    })
                });
                
                let data = await res.json();
                this.customersList.push(data); // Update list tabel
                this.selectCustomerFromList(data); // Auto select
                this.newCustomerName = ''; 
                this.newCustomerPhone = '';
            } catch(e) { 
                alert('Gagal simpan member.'); 
            }
        },

        editCustomer(cust) {
            this.editingCustId = cust.id;
            this.editCustName = cust.name;
            this.editCustPhone = cust.phone;
        },

        async updateCustomer(id) {
             if(this.editCustPhone && !/^\d+$/.test(this.editCustPhone)) {
                    alert('Nomor HP harus berupa angka');
                    return;
                }
                
                if(this.editCustPhone && this.editCustPhone.length < 10) {
                    alert('Nomor HP minimal 10 digit');
                    return;
                }
            try {
                let res = await fetch(`{{ url('pos/customer') }}/${id}`, {
                    method: 'PATCH', 
                    headers: {
                        'Content-Type':'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        name: this.editCustName, 
                        phone: this.editCustPhone 
                    })
                });
                
                let data = await res.json();
                this.editingCustId = null;
                this.loadCustomers(); // Refresh list
                
                // Jika customer ini sedang dipilih, update tampilannya
                if (this.selectedCustomer && this.selectedCustomer.id === id) {
                    this.selectCustomerFromList(data);
                }
            } catch(e) { 
                alert('Gagal update member.'); 
            }
        },

        async deleteCustomer(id) {
            if(!confirm('Hapus member ini?')) return;
            
            try {
                let res = await fetch(`{{ url('pos/customer') }}/${id}`, {
                    method: 'DELETE', 
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if(!res.ok) {
                    let err = await res.json();
                    return alert(err.message);
                }
                
                this.loadCustomers();
                if (this.selectedCustomer && this.selectedCustomer.id === id) {
                    this.resetCustomer();
                }
            } catch(e) { 
                alert('Gagal hapus member.'); 
            }
        },

        selectCustomerFromList(cust) {
            this.selectedCustomer = cust;
            this.customerSearchInput = cust.name + ' (' + (cust.phone||'-') + ')';
            this.customerFeedback = 'OK';
            this.customerFeedbackColor = 'text-green-600';
            this.showCustomerModal = false;
        },
        
        resetCustomer() {
            this.selectedCustomer = null;
            this.customerSearchInput = '';
            this.customerFeedback = '';
        },


        // --- HISTORY MANAGEMENT ---
        async loadHistory() {
            try {
                const params = new URLSearchParams({
                    q: this.historyFilter.q,
                    user_id: this.historyFilter.user_id,
                    start_date: this.historyFilter.start_date,
                    end_date: this.historyFilter.end_date
                });
                
                const response = await fetch(`{{ route('pos.history.json') }}?${params.toString()}`);
                const data = await response.json();
                
                this.historyData = data.data || [];
            } catch(error) {
                console.error('Error loading history:', error);
                this.historyData = [];
            }
        },

         // --- ONLINE ORDER LOGIC ---
// --- ONLINE ORDER LOGIC ---
async viewOnlineOrder(id) {
    try {
        const csrfToken = this.getCsrfToken();
        
        // ✅ GUNAKAN URL YANG BENAR SESUAI ROUTES
        const url = `/pos/online-order/${id}`;
        
        console.log('Fetching order from:', url);
        
        const res = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        }); 
        
        if (!res.ok) {
            throw new Error(`HTTP ${res.status} - ${res.statusText}`);
        }
        
        const data = await res.json();
        this.onlineOrder = data;
        this.onlinePayAmount = data.total_amount ? data.total_amount.toString() : '0';
        this.showOnlineDetailModal = true;
        this.isProcessingOnline = false;
        
    } catch (e) { 
        console.error('Error loading order:', e);
        alert('Gagal memuat detail pesanan: ' + e.message); 
    }
},

// Hitung Kembalian Online
            get onlineChangeAmount() {
                if (!this.onlineOrder) return 0;
                let pay = Number(this.onlinePayAmount) || 0;
                let total = Number(this.onlineOrder.total_amount) || 0;
                return pay - total;
            },
            
async processOnlineOrder() {
    // Validasi
    if (this.onlineChangeAmount < 0) {
        alert('Uang Pembayaran Kurang!');
        return;
    }
    
    if (!confirm('Pastikan uang sudah diterima dan barang sudah siap. Lanjutkan?')) return;

    this.isProcessingOnline = true;
    
    try {
        const csrfToken = this.getCsrfToken();
        
        // ✅ GUNAKAN URL YANG BENAR SESUAI ROUTES
        const url = `/pos/online-order/${this.onlineOrder.id}/process`;
        
        console.log('Processing to:', url);
        
        const res = await fetch(url, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                pay_amount: parseFloat(this.onlinePayAmount),
                change_amount: this.onlineChangeAmount
            })
        });
        
        if (!res.ok) {
            throw new Error(`HTTP ${res.status} - ${res.statusText}`);
        }
        
        const data = await res.json();
        
        if(data.status === 'success') {
            // Cetak struk
            const invoiceToPrint = data.invoice_number || this.onlineOrder.invoice_number;
            this.printReceipt(invoiceToPrint);
            
            // Tampilkan success message
            alert('Pesanan online berhasil diproses!\nKembalian: ' + this.formatRupiah(this.onlineChangeAmount));
            
            // Tutup modal dan refresh
            this.showOnlineDetailModal = false;
            
            // Refresh halaman setelah delay singkat
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            
        } else { 
            throw new Error(data.message || 'Unknown server error'); 
        }
    } catch(e) { 
        console.error('Process online error:', e);
        alert('Error: ' + e.message); 
    } finally { 
        this.isProcessingOnline = false; 
    }
},

// [BARU] Reject Order (untuk kasir)
    async rejectOrder(orderId) {
        if (!confirm('Tolak pesanan ini? Pesanan akan dibatalkan dan customer akan mendapat notifikasi.')) return;

        try {
            const csrfToken = this.getCsrfToken();
            const url = `/pos/online-order/${orderId}/reject`;
            
            const res = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await res.json();
            
            if(data.status === 'success') {
                alert('Pesanan berhasil ditolak');
                window.location.reload();
            } else {
                alert('Gagal: ' + data.message);
            }
        } catch(e) {
            console.error('Reject order error:', e);
            alert('Error sistem');
        }
    },

    // [UPDATE] Update Order Status - hanya untuk process dan completed
    async updateOrderStatus(orderId, newStatus) {
        if (!confirm(`Ubah status pesanan menjadi ${newStatus.toUpperCase()}?`)) return;

        try {
            const csrfToken = this.getCsrfToken();
            const url = `/pos/online-order/${orderId}/update-status`;
            
            const res = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    status: newStatus
                })
            });
            
            const data = await res.json();
            
            if(data.status === 'success') {
                alert('Status berhasil diupdate');
                window.location.reload();
            } else {
                alert('Gagal: ' + data.message);
            }
        } catch(e) {
            console.error('Update status error:', e);
            alert('Error sistem');
        }
    },

        // --- UTILITIES ---
        printReceipt(invoiceNumber) {
            const printUrl = `{{ url('/pos/print') }}/${invoiceNumber}`;
            window.open(printUrl, '_blank', 'width=400,height=600');
        },
        
        
        formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        }

    }));
});
</script>