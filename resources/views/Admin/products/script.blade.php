<script>
    function clearMessages(manager, timeout = 3000) {
        setTimeout(() => {
            manager.globalCategoryError = ''; manager.globalCategorySuccess = '';
            manager.globalUnitError = ''; manager.globalUnitSuccess = '';
        }, timeout);
    }

    function productFormManager(initialStatuses = {}) {
        return {
            // DATA MASTER
            masterCategories: [],
            masterUnits: [], 
            
            // KONTROL MODAL
            showCreateModal: {{ $errors->any() && session('form_type') !== 'edit' ? 'true' : 'false' }},
            showEditModal: {{ $errors->any() && session('form_type') === 'edit' ? 'true' : 'false' }},
            showCategoryModal: false,
            showUnitModal: false,
            showDeleteModal: false,
            statusCache: initialStatuses,
            isSaving: false,

            // FORM DATA
            form: { id: null, name: '{{ old('name') }}', kode_produk: '{{ old('kode_produk') }}', category_id: '{{ old('category_id') }}', status: '{{ old('status', 'active') }}', description: '{{ old('description') }}', foto_url: '' },
            productRows: [], baseRowIndex: 0, baseUnitName: 'Pcs', editFormAction: '',
            
            // MODAL KECIL
            newCategoryName: '', editCategoryId: null, editCategoryName: '',
            newUnitName: '', newUnitShortName: '', editUnitId: null, editUnitName: '', editUnitShortName: '',
            errors: { category: '', unit: '' }, categoryErrors: { name: '', edit_name: '' }, unitErrors: { name: '', short_name: '', edit_name: '', edit_short_name: '' },
            globalCategoryError: '', globalCategorySuccess: '', globalUnitError: '', globalUnitSuccess: '',
            deleteProductId: null, deleteProductName: '', deleteFormAction: '',

            // --- INIT ---
            async init() {
                // Load master data selalu di awal
                await this.loadMasterData();

                @php
                    $oldUnits = old('units');
                    $defaultUnit = [['unit_id' => '', 'price' => '', 'conversion' => 1, 'is_base_unit' => true, 'wholesale' => []]];
                    $createUnitsData = $oldUnits ? array_values($oldUnits) : $defaultUnit;
                    $initBaseIndex = old('is_base_unit_index', 0);
                @endphp

                if(!this.showEditModal) {
                    this.productRows = @json($createUnitsData).map((row, idx) => ({
                        unit_id: row.unit_id ? parseInt(row.unit_id) : '', 
                        price: row.price, 
                        conversion: row.conversion, 
                        is_base_unit: (idx == {{ $initBaseIndex }}),
                        wholesale: row.wholesale || [],
                        showWholesale: false
                    }));
                    this.baseRowIndex = {{ $initBaseIndex }};
                    this.$nextTick(() => this.refreshUnitNames());
                }

                if (this.showEditModal) {
                    let oldId = '{{ old('product_id') }}';
                    if(oldId) this.editFormAction = `{{ url('admin/products') }}/${oldId}`;
                }
            },

            async loadMasterData() {
                try {
                    const [catRes, unitRes] = await Promise.all([
                        fetch('{{ route('admin.categories.json') }}'),
                        fetch('{{ route('admin.units.json') }}')
                    ]);
                    this.masterCategories = await catRes.json();
                    this.masterUnits = await unitRes.json();
                } catch (e) { console.error('Gagal load master data'); }
            },

            // --- FUNGSI GROSIR ---
            addWholesale(rowIndex) {
                this.productRows[rowIndex].wholesale.push({ min_qty: '', price: '' });
            },

            removeWholesale(rowIndex, wholesaleIndex) {
                this.productRows[rowIndex].wholesale.splice(wholesaleIndex, 1);
            },

            toggleWholesale(rowIndex) {
                this.productRows[rowIndex].showWholesale = !this.productRows[rowIndex].showWholesale;
            },

            // --- EDIT MODAL (FIX DELAY & REFRESH) ---
            async openEditModal(id) {
                // Selalu refresh data master saat buka edit
                await this.loadMasterData();

                try {
                    const res = await fetch(`{{ url('admin/products') }}/${id}/edit-json`);
                    const data = await res.json();
                    
                    this.editFormAction = `{{ url('admin/products') }}/${id}`;
                    
                    this.form.id = data.id;
                    this.form.name = data.name;
                    this.form.kode_produk = data.kode_produk;
                    this.form.category_id = data.category_id ? parseInt(data.category_id) : '';
                    this.form.status = data.status;
                    this.form.description = data.description;
                    this.form.foto_url = data.foto_produk ? `{{ Storage::url('') }}${data.foto_produk}` : '';

                    // Mapping data dengan wholesale
                    let mappedRows = data.units.map(u => ({
                        unit_id: parseInt(u.unit_id), 
                        price: parseFloat(u.price), 
                        conversion: parseInt(u.conversion_to_base),
                        is_base_unit: u.is_base_unit == 1,
                        wholesale: u.wholesale_prices ? u.wholesale_prices.map(wp => ({
                            min_qty: wp.min_qty,
                            price: wp.price
                        })) : [],
                        showWholesale: false
                    }));

                    this.baseRowIndex = mappedRows.findIndex(r => r.is_base_unit);
                    if (this.baseRowIndex === -1) this.baseRowIndex = 0;
                    
                    this.productRows = mappedRows;

                    // [FIX GACOR] Kasih jeda 100ms sebelum modal tampil
                    setTimeout(() => {
                        this.refreshUnitNames();
                        this.showEditModal = true; 
                    }, 100);

                } catch (e) { alert('Gagal load data edit.'); console.error(e); }
            },

            // --- NAVIGASI KEYBOARD (FIX SKIP TOMBOL +) ---
            focusNext(e) {
                const inputs = Array.from(document.querySelectorAll('input:not([type="hidden"]), select, textarea, button[type="submit"], button[type="button"]:not([disabled]):not([tabindex="-1"])'))
                                    .filter(el => !el.disabled && el.offsetParent !== null);
                const index = inputs.indexOf(e.target);
                if (index > -1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                    if(inputs[index + 1].tagName === 'INPUT') inputs[index + 1].select();
                }
            },

            // Navigasi Grid (Atas/Bawah/Kiri/Kanan)
            focusGrid(e, rowIndex, fieldName) {
                if (rowIndex < 0 || rowIndex >= this.productRows.length) return;
                
                let wrapper = this.showCreateModal ? document.getElementById('createModal') : document.getElementById('editModal');
                if(!wrapper) return;

                const selector = `[name="units[${rowIndex}][${fieldName}]"]`;
                const targetInput = wrapper.querySelector(selector);

                if (targetInput) {
                    targetInput.focus();
                    if(targetInput.tagName === 'INPUT') targetInput.select();
                }
            },

            // --- FUNGSI CRUD UNIT ---
            addUnit() { 
                this.productRows.push({ 
                    unit_id: '', 
                    price: '', 
                    conversion: '', 
                    is_base_unit: false, 
                    wholesale: [],
                    showWholesale: false
                }); 
                this.$nextTick(() => this.focusGrid(null, this.productRows.length - 1, 'unit_id')); 
            },

            removeUnit(index) { 
                if (this.productRows.length <= 1) return; 
                let wasBase = this.productRows[index].is_base_unit; 
                this.productRows.splice(index, 1); 
                if (wasBase) this.setBaseUnit(0); 
            },

            setBaseUnit(index) { 
                this.productRows.forEach((row, i) => { 
                    row.is_base_unit = (i === index); 
                    if (i === index) row.conversion = 1; 
                }); 
                this.baseRowIndex = index; 
                this.refreshUnitNames(); 
            },

            refreshUnitNames() { 
                let baseRow = this.productRows[this.baseRowIndex]; 
                if(baseRow && baseRow.unit_id) { 
                    let u = this.masterUnits.find(m => m.id == baseRow.unit_id); 
                    this.baseUnitName = u ? u.short_name : 'Pcs'; 
                } else { 
                    this.baseUnitName = 'Pcs'; 
                } 
            },

            onUnitChange(index) { 
                this.refreshUnitNames(); 
            },

            openCreateModal() { 
                this.showCreateModal = true; 
                this.form = { 
                    id: null, 
                    name: '', 
                    kode_produk: '', 
                    category_id: '', 
                    status: 'active', 
                    description: '', 
                    foto_url: '' 
                }; 
                this.productRows = [{ 
                    unit_id: '', 
                    price: '', 
                    conversion: 1, 
                    is_base_unit: true, 
                    wholesale: [],
                    showWholesale: false
                }]; 
                this.baseRowIndex = 0; 
                this.refreshUnitNames(); 
            },

            // --- FUNGSI LAINNYA ---
            async toggleStatus(id) { 
                try { 
                    let currentStatus = this.statusCache[id]; 
                    let newStatus = currentStatus === 'active' ? 'inactive' : 'active'; 
                    this.statusCache[id] = newStatus; 
                    const res = await fetch(`{{ url('admin/products') }}/${id}/toggle-status`, { 
                        method: 'POST', 
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } 
                    }); 
                    if(!res.ok) { 
                        this.statusCache[id] = currentStatus; 
                        alert('Gagal ubah status.'); 
                    } 
                } catch(e) { 
                    console.error(e); 
                } 
            },

            async saveNewCategory() { 
                this.isSaving = true; 
                this.categoryErrors = { name: '' }; 
                this.globalCategoryError = ''; 
                try { 
                    const res = await fetch('{{ route('admin.categories.storeAjax') }}', { 
                        method: 'POST', 
                        headers: {
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }, 
                        body: JSON.stringify({ name: this.newCategoryName }) 
                    }); 
                    const data = await res.json(); 
                    if (!res.ok) { 
                        if(res.status===422) this.categoryErrors.name = data.errors.name?.[0] || ''; 
                        else this.globalCategoryError = data.message || 'Gagal.'; 
                    } else { 
                        this.masterCategories.push(data); 
                        this.form.category_id = data.id; 
                        this.showCategoryModal = false; 
                        this.newCategoryName = ''; 
                    } 
                } catch(e) { 
                    this.globalCategoryError = 'Error jaringan'; 
                } finally { 
                    this.isSaving = false; 
                } 
            },

            async saveNewUnit() { 
                this.isSaving = true; 
                this.unitErrors = { name: '', short_name: '' }; 
                this.globalUnitError = ''; 
                try { 
                    const res = await fetch('{{ route('admin.units.storeAjax') }}', { 
                        method: 'POST', 
                        headers: {
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }, 
                        body: JSON.stringify({ 
                            name: this.newUnitName, 
                            short_name: this.newUnitShortName 
                        }) 
                    }); 
                    const data = await res.json(); 
                    if (!res.ok) { 
                        if(res.status===422) { 
                            this.unitErrors.name = data.errors.name?.[0] || ''; 
                            this.unitErrors.short_name = data.errors.short_name?.[0] || ''; 
                        } else this.globalUnitError = data.message || 'Gagal.'; 
                    } else { 
                        this.masterUnits.push(data); 
                        this.showUnitModal = false; 
                        this.newUnitName = ''; 
                        this.newUnitShortName = ''; 
                    } 
                } catch(e) { 
                    this.globalUnitError = 'Error jaringan'; 
                } finally { 
                    this.isSaving = false; 
                } 
            },

            startEditCategory(cat) { 
                this.editCategoryId = cat.id; 
                this.editCategoryName = cat.name; 
            }, 

            cancelEditCategory() { 
                this.editCategoryId = null; 
            }, 

            async saveEditCategory(id) { 
                try { 
                    const res = await fetch(`{{ url('admin/categories-ajax') }}/${id}`, { 
                        method: 'PATCH', 
                        headers: {
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }, 
                        body: JSON.stringify({ name: this.editCategoryName }) 
                    }); 
                    if(!res.ok) throw await res.json(); 
                    await this.loadMasterData(); 
                    this.cancelEditCategory(); 
                } catch(e) { 
                    alert('Gagal update.'); 
                } 
            }, 

            async deleteCategory(id) { 
                if(!confirm('Hapus?')) return; 
                try { 
                    const res = await fetch(`{{ url('admin/categories-ajax') }}/${id}`, { 
                        method: 'DELETE', 
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} 
                    }); 
                    if(!res.ok) throw await res.json(); 
                    this.masterCategories = this.masterCategories.filter(c => c.id != id); 
                } catch(e) { 
                    alert(e.message || 'Gagal.'); 
                } 
            },

            startEditUnit(u) { 
                this.editUnitId = u.id; 
                this.editUnitName = u.name; 
                this.editUnitShortName = u.short_name; 
            }, 

            cancelEditUnit() { 
                this.editUnitId = null; 
            }, 

            async saveEditUnit(id) { 
                try { 
                    const res = await fetch(`{{ url('admin/units-ajax') }}/${id}`, { 
                        method: 'PATCH', 
                        headers: {
                            'Content-Type': 'application/json', 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }, 
                        body: JSON.stringify({ 
                            name: this.editUnitName, 
                            short_name: this.editUnitShortName 
                        }) 
                    }); 
                    if(!res.ok) throw await res.json(); 
                    await this.loadMasterData(); 
                    this.cancelEditUnit(); 
                } catch(e) { 
                    alert('Gagal update.'); 
                } 
            }, 

            async deleteUnit(id) { 
                if(!confirm('Hapus?')) return; 
                try { 
                    const res = await fetch(`{{ url('admin/units-ajax') }}/${id}`, { 
                        method: 'DELETE', 
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} 
                    }); 
                    if(!res.ok) throw await res.json(); 
                    this.masterUnits = this.masterUnits.filter(u => u.id != id); 
                } catch(e) { 
                    alert(e.message || 'Gagal.'); 
                } 
            },

            openDeleteModal(id, name) { 
                this.deleteFormAction = `{{ url('admin/products') }}/${id}`; 
                this.deleteProductName = name; 
                this.showDeleteModal = true; 
            }
        }
    }
</script>