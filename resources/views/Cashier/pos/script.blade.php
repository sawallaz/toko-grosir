<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posSystem', () => ({
        // STATE AWAL
        tab: 'sales',
        lastInvoiceNumber: '{{ $lastInvoiceNumber ?? "" }}',
        showCustomerModal: false, 
        
        // DATA TRANSAKSI
        cart: [{
            tempId: Date.now(), 
            product_name: '', 
            product_id: null, 
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
        
        // CUSTOMER
        customerSearchInput: '', 
        selectedCustomer: null,
       
        customersList: [],
        newCustomerName: '', 
        newCustomerPhone: '',

        // PAYMENT
        payAmount: '', 
        isProcessing: false,

        // WAKTU & NOTIFIKASI
        currentDate: '',
        currentTime: '',
        timeInterval: null,
        badgeInterval: null,
        
        // NOTIFIKASI BADGE GLOBAL
        globalOnlineCount: 0,
        
        // HISTORY
        historyData: [], 
        historyFilter: { q: '', user_id: '', start_date: '', end_date: '' },

        // ONLINE ORDERS
        onlineOrders: [],
        onlineSearch: '',
        loadingOnlineOrders: false,
        showOnlineDetailModal: false,
        onlineOrder: null,
        onlinePayAmount: '0',
        isProcessingOnline: false,

        // EDIT CUSTOMER
        editingCustId: null,
        editCustName: '',
        editCustPhone: '',

        // INITIALIZATION
        init() {
            console.log('💳 POS System Initialized');
            
            // Initialize waktu real-time
            this.updateTime();
            this.timeInterval = setInterval(() => this.updateTime(), 1000);
            
            // START BADGE AUTO UPDATE
            this.startBadgeAutoUpdate();
            
            // Load badge count segera
            this.updateBadgeCount();
            
            this.$watch('tab', (value) => {
                if (value === 'sales') {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const firstInput = document.querySelector('input[placeholder*="barcode"]');
                            if (firstInput) firstInput.focus();
                        }, 100);
                    });
                }
                
                if (value === 'history') {
                    this.loadHistory();
                }
                
                if (value === 'online') {
                    this.loadOnlineOrders();
                }
            });
            
            // Setup keyboard shortcuts
            this.setupKeyboardShortcuts();
            
            // Focus awal
            if (this.tab === 'sales') {
                this.$nextTick(() => {
                    const firstInput = document.querySelector('input[placeholder*="barcode"]');
                    if (firstInput) firstInput.focus();
                });
            }
        },

        // BADGE AUTO UPDATE
        startBadgeAutoUpdate() {
            this.badgeInterval = setInterval(() => {
                this.updateBadgeCount();
            }, 15000);
        },
        
        // Update badge count dari server
        async updateBadgeCount() {
            try {
                const response = await fetch('{{ route("pos.online.order.count") }}');
                const data = await response.json();
                this.globalOnlineCount = data.total;
            } catch (error) {
                console.error('Badge update error:', error);
            }
        },

        // Update waktu untuk sidebar
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

        // Setup keyboard shortcuts
        setupKeyboardShortcuts() {
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
                // F3 - Tab Online
                if(e.key === 'F3') { 
                    e.preventDefault(); 
                    this.switchTab('online');
                }
                // F5 - Refresh
                if(e.key === 'F5') { 
                    e.preventDefault(); 
                    if(this.tab === 'online') {
                        this.loadOnlineOrders();
                    } else if(this.tab === 'history') {
                        this.loadHistory();
                    } else if(this.tab === 'sales') {
                        this.loadLastInvoice();
                    }
                }
                // F8 - Modal Customer
                if(e.key === 'F8') { 
                    e.preventDefault(); 
                    this.openCustomerModal();
                }
                // F10 - Bayar
                if(e.key === 'F10') { 
                    e.preventDefault(); 
                    if(this.tab === 'sales') this.processPayment();
                }
                // ESC - Close modal atau reset customer
                if(e.key === 'Escape') {
                    if(this.showCustomerModal) {
                        this.showCustomerModal = false;
                    } else if(this.showOnlineDetailModal) {
                        this.showOnlineDetailModal = false;
                    } else if(this.selectedCustomer) {
                        this.resetCustomer();
                    }
                }
            });
        },

        // Load last invoice untuk sidebar
        async loadLastInvoice() {
            try {
                const response = await fetch('{{ route("pos.index") }}?last_invoice=1');
                const data = await response.json();
                if(data.last_invoice) {
                    this.lastInvoiceNumber = data.last_invoice;
                }
            } catch(error) {
                console.error('Error loading last invoice:', error);
            }
        },

        // Switch tab
        switchTab(tabName) {
            this.tab = tabName;
            if (tabName === 'online') {
                this.updateBadgeCount();
            }
        },

        // AUTO RESET SETELAH BAYAR
        resetAfterPayment() {
            this.cart = [{
                tempId: Date.now(), 
                product_name: '', 
                product_id: null, 
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
            }];
            
            this.payAmount = ''; 
            this.selectedCustomer = null; 
            this.customerSearchInput = '';
            
            this.loadLastInvoice();
            
            this.$nextTick(() => {
                const firstInput = document.querySelector('input[placeholder*="barcode"]');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        },

        // ============ FUNGSI TRANSAKSI ============
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
                row.results = await response.json();
                row.showResults = true; 
                row.focusIndex = -1;
            } catch (error) {
                row.results = [];
                row.showResults = false;
            }
        },

        selectProduct(index, product) {
            let row = this.cart[index];
            
            row.product_id = product.id;
            row.product_name = product.name;
            row.available_units = product.units || [];
            row.results = []; 
            row.showResults = false;
            row.error = false;

            // Auto select unit
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
                const inputs = document.querySelectorAll(`input[type="number"]`);
                if(inputs[index]) {
                    inputs[index].focus();
                    inputs[index].select();
                }
            });
        },
        
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
                this.selectProduct(index, row.results[row.focusIndex]); 
            }
        },

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
        
        // FUNGSI UPDATE HARGA DENGAN GROSIR - DIPERBAIKI
        updatePrice(index) {
            let row = this.cart[index];
            
            if (!row.product_id) {
                row.price = 0;
                row.subtotal = 0;
                row.isWholesale = false;
                return;
            }
            
            let unitData = row.available_units.find(u => u.product_unit_id == row.product_unit_id);
            if (!unitData) {
                row.subtotal = 0;
                return;
            }
            
            let basePrice = Number(unitData.price);
            let qty = row.qty || 1;
            let appliedPrice = basePrice;
            let isWholesale = false;
            
            // CEK HARGA GROSIR - urutkan dari min_qty terbesar ke terkecil
            if(unitData.wholesale && unitData.wholesale.length > 0) {
                // Sort descending by min_qty
                let sortedWholesale = [...unitData.wholesale].sort((a, b) => b.min - a.min);
                
                for(let wholesaleRule of sortedWholesale) {
                    if(qty >= wholesaleRule.min) {
                        appliedPrice = Number(wholesaleRule.price);
                        isWholesale = true;
                        break;
                    }
                }
            }
            
            row.price = appliedPrice;
            row.isWholesale = isWholesale;
            row.subtotal = qty * appliedPrice;
        },

        updateSubtotal(index) {
            this.updatePrice(index);
        },

        addRow() {
            const newRow = {
                tempId: Date.now(), 
                product_name: '', 
                product_id: null, 
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
                const inputs = document.querySelectorAll('input[placeholder*="barcode"]');
                if(inputs.length > 0) {
                    const lastInput = inputs[inputs.length - 1];
                    lastInput.focus();
                }
            });
        },
        
        removeRow(index) {
            if (this.cart.length > 1) {
                this.cart.splice(index, 1);
            } else {
                this.cart[0] = { 
                    tempId: Date.now(), 
                    product_name: '', 
                    product_id: null, 
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
                const firstInput = document.querySelector('input[placeholder*="barcode"]');
                if (firstInput) firstInput.focus();
            });
        },

        // Fokus ke field berikutnya
        focusNextField(index, target) {
            if (target === 'qty') {
                this.$nextTick(() => {
                    const qtyInput = document.querySelectorAll(`input[type="number"]`)[index];
                    if (qtyInput) {
                        qtyInput.focus();
                        qtyInput.select();
                    }
                });
            } else if (target === 'add') {
                // Jika di row terakhir, tambah row baru
                if (index === this.cart.length - 1) {
                    this.addRow();
                } else {
                    // Fokus ke input pencarian di row berikutnya
                    this.$nextTick(() => {
                        const nextSearchInput = document.querySelectorAll(`input[placeholder*="barcode"]`)[index + 1];
                        if (nextSearchInput) {
                            nextSearchInput.focus();
                        }
                    });
                }
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

        // PROSES PEMBAYARAN (AUTO RESET)
        async processPayment() {
            if (this.isProcessing) return;
            
            let validCart = this.cart.filter(row => row.product_id && row.qty > 0);
            if (validCart.length === 0) {
                alert('❌ Keranjang kosong! Tambahkan produk terlebih dahulu.');
                return;
            }
            
            if (Number(this.payAmount) < this.grandTotal) {
                alert('❌ Uang yang dibayarkan kurang!');
                return;
            }
            
            this.isProcessing = true;
            
            try {
                const payload = {
                    cart: validCart.map(item => ({
                        product_unit_id: item.product_unit_id,
                        qty: item.qty,
                        price: item.price // Harga sudah termasuk grosir
                    })),
                    total_amount: this.grandTotal,
                    pay_amount: this.payAmount,
                    customer_id: this.selectedCustomer?.id || null
                };
                
                const response = await fetch('{{ route("pos.store") }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                
                if(data.status === 'success') {
                    // Update last invoice number di sidebar
                    this.lastInvoiceNumber = data.invoice_number;
                    
                    let message = '✅ Transaksi berhasil!\nInvoice: ' + data.invoice_number;
                    if(this.changeAmount >= 0) {
                        message += '\nKembalian: ' + this.formatRupiah(this.changeAmount);
                    }
                    
                    // TANYA CETAK STRUK
                    const shouldPrint = confirm(message + '\n\nCetak struk sekarang?');
                    
                    if (shouldPrint) {
                        this.printReceipt(data.invoice_number);
                    }
                    
                    // AUTO RESET SETELAH BAYAR
                    this.resetAfterPayment();
                    
                } else {
                    alert('❌ ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch(error) {
                alert('❌ Error sistem: ' + error.message);
            } finally {
                this.isProcessing = false;
            }
        },

        // ============ FUNGSI CUSTOMER ============
        async findCustomer() {
            if (!this.customerSearchInput.trim()) {
                return;
            }
            
            try {
                const response = await fetch(`{{ route('pos.customer.search') }}?q=${encodeURIComponent(this.customerSearchInput)}`);
                const data = await response.json();
                
                if(data.length > 0) {
                    this.selectedCustomer = data[0];
                    this.customerSearchInput = data[0].name + (data[0].phone ? ' (' + data[0].phone + ')' : '');
                } else {
                    this.selectedCustomer = null;
                    alert('Member tidak ditemukan');
                }
            } catch(error) {
                console.error('Customer search error:', error);
            }
        },

        openCustomerModal() {
            this.showCustomerModal = true;
            this.loadCustomers();
        },
        
        async loadCustomers() {
            try {
                let res = await fetch('{{ route("pos.customer.list") }}');
                this.customersList = await res.json();
            } catch(e) {
                alert('Gagal memuat data member');
            }
        },

        async saveCustomer() {
            if(!this.newCustomerName.trim()) {
                alert('Nama wajib diisi');
                return;
            }
            
            if(this.newCustomerPhone && !/^\d+$/.test(this.newCustomerPhone)) {
                alert('Nomor HP harus berupa angka');
                return;
            }
            
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
                this.customersList.push(data);
                this.selectedCustomer = data;
                this.customerSearchInput = data.name + (data.phone ? ' (' + data.phone + ')' : '');
                this.showCustomerModal = false;
                this.newCustomerName = ''; 
                this.newCustomerPhone = '';
            } catch(e) { 
                alert('❌ Gagal simpan member'); 
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
                this.loadCustomers();
                
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
                    this.selectedCustomer = null;
                    this.customerSearchInput = '';
                }
            } catch(e) { 
                alert('Gagal hapus member.'); 
            }
        },

        selectCustomerFromList(cust) {
            this.selectedCustomer = cust;
            this.customerSearchInput = cust.name + (cust.phone ? ' (' + cust.phone + ')' : '');
            this.showCustomerModal = false;
        },
        
        resetCustomer() {
            this.selectedCustomer = null;
            this.customerSearchInput = '';
        },

        // ============ FUNGSI HISTORY ============
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
                console.error('History load error:', error);
                this.historyData = [];
            }
        },

        // ============ FUNGSI ONLINE ORDERS ============
        async loadOnlineOrders() {
            this.loadingOnlineOrders = true;
            try {
                this.onlineOrders = [];
                const params = new URLSearchParams();
                if (this.onlineSearch) {
                    params.append('q', this.onlineSearch);
                }
                
                const response = await fetch(`{{ route("pos.online.orders.json") }}?${params.toString()}`);
                const data = await response.json();
                
                this.onlineOrders = data.data || [];
                
                // Update badge count setelah load data
                this.updateBadgeCount();
                
            } catch (error) {
                console.error('Online orders load error:', error);
                this.onlineOrders = [];
                alert('Gagal memuat pesanan online');
            } finally {
                this.loadingOnlineOrders = false;
            }
        },

        // TAMPILKAN DETAIL PESANAN
        async viewOrderDetails(id) {
            try {
                const url = `{{ route('pos.online.order.detail', ['id' => ':id']) }}`.replace(':id', id);
                const response = await fetch(url);
                const data = await response.json();
                this.onlineOrder = data;
                this.onlinePayAmount = '0';
                this.showOnlineDetailModal = true;
                this.isProcessingOnline = false;
            } catch (e) { 
                alert('Gagal memuat detail pesanan'); 
            }
        },

        // VIEW MODAL UNTUK BAYAR
        async viewOnlineOrder(id) {
            try {
                const url = `{{ route('pos.online.order.detail', ['id' => ':id']) }}`.replace(':id', id);
                const response = await fetch(url);
                const data = await response.json();
                this.onlineOrder = data;
                this.onlinePayAmount = data.total_amount ? data.total_amount.toString() : '0';
                this.showOnlineDetailModal = true;
                this.isProcessingOnline = false;
            } catch (e) { 
                alert('Gagal memuat detail pesanan'); 
            }
        },

        get onlineChangeAmount() {
            if (!this.onlineOrder) return 0;
            let pay = Number(this.onlinePayAmount) || 0;
            let total = Number(this.onlineOrder.total_amount) || 0;
            return pay - total;
        },
        
        async processOnlineOrder() {
            if (this.onlineChangeAmount < 0) {
                alert('❌ Uang Pembayaran Kurang!');
                return;
            }
            
            if (!confirm('✅ Pastikan uang sudah diterima dan barang sudah siap. Lanjutkan?')) return;

            this.isProcessingOnline = true;
            
            try {
                const url = `{{ route('pos.online.order.process', ['id' => ':id']) }}`.replace(':id', this.onlineOrder.id);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        pay_amount: parseFloat(this.onlinePayAmount),
                        change_amount: this.onlineChangeAmount
                    })
                });
                
                const data = await response.json();
                
                if(data.status === 'success') {
                    const invoiceToPrint = data.invoice_number || this.onlineOrder.invoice_number;
                    
                    // CETAK STRUK UNTUK PESANAN ONLINE
                    this.printReceipt(invoiceToPrint);
                    
                    alert('✅ Pesanan online berhasil diproses!\nKembalian: ' + this.formatRupiah(this.onlineChangeAmount));
                    
                    this.showOnlineDetailModal = false;
                    this.loadOnlineOrders();
                    this.updateBadgeCount();
                    
                } else { 
                    alert('❌ ' + (data.message || 'Gagal memproses')); 
                }
            } catch(e) { 
                alert('❌ Error sistem'); 
            } finally { 
                this.isProcessingOnline = false; 
            }
        },

        async rejectOrder(orderId) {
            if (!confirm('❌ Tolak pesanan ini? Pesanan akan dibatalkan.')) return;

            try {
                const url = `{{ route('pos.online.order.reject', ['id' => ':id']) }}`.replace(':id', orderId);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if(data.status === 'success') {
                    alert('✅ Pesanan berhasil ditolak');
                    this.loadOnlineOrders();
                    this.updateBadgeCount();
                } else {
                    alert('❌ Gagal: ' + data.message);
                }
            } catch(e) {
                alert('❌ Error sistem');
            }
        },

        async updateOrderStatus(orderId, newStatus) {
            if (!confirm(`🔄 Ubah status pesanan menjadi ${newStatus.toUpperCase()}?`)) return;

            try {
                const url = `{{ route('pos.online.order.updateStatus', ['id' => ':id']) }}`.replace(':id', orderId);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                });
                
                const data = await response.json();
                
                if(data.status === 'success') {
                    alert('✅ Status berhasil diupdate');
                    this.loadOnlineOrders();
                    this.updateBadgeCount();
                } else {
                    alert('❌ Gagal: ' + data.message);
                }
            } catch(e) {
                alert('❌ Error sistem: ' + e.message);
            }
        },

        // ============ UTILITIES ============
        printReceipt(invoiceNumber) {
            if (!invoiceNumber) return;
            
            const printUrl = `{{ url('/pos/print') }}/${invoiceNumber}`;
            
            const printWindow = window.open(printUrl, '_blank', 'width=400,height=600');
            
            printWindow.onload = function() {
                printWindow.print();
            };
        },
        
        formatRupiah(amount) {
            if (!amount && amount !== 0) return '0';
            return new Intl.NumberFormat('id-ID').format(amount);
        }

    }));
});
</script>