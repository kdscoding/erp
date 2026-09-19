import {
    createItemMap,
    rowTemplate,
    reindex,
    getEnteredCodes,
    isDuplicateCode,
    clearItemInfo,
    fillItemInfo,
    updateRowSubtotal,
    toggleRemarks,
    parseNumber,
    formatNumber,
} from './po-item-rows';

export function initPoCreate(config = {}) {
    const { items = [], oldItems = [], searchUrl = '' } = config;
    const $ = window.jQuery;
    const tbody = document.querySelector('#po-items-table tbody');
    const addBtn = document.getElementById('btn-add-item');
    const grandTotalText = document.getElementById('grand-total-text');
    const grandTotalInput = document.getElementById('grand-total-input');
    const itemCountText = document.getElementById('item-count-text');
    const form = document.getElementById('po-form');
    const alertBanner = document.getElementById('po-create-alert');
    const notesToggle = document.getElementById('btn-toggle-notes');
    const notesFieldWrapper = document.getElementById('notesFieldWrapper');
    const itemMap = createItemMap(items);
    const datalist = document.getElementById('item-codes');
    let submitLocked = false;
    let searchTimer = null;

    function moveFocusToNextRow(currentTr) {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const currentIdx = rows.indexOf(currentTr);
        const nextTr = rows[currentIdx + 1] || rows[rows.length - 1];
        if (nextTr) {
            const nextCodeInput = nextTr.querySelector('.item-code-input');
            if (nextCodeInput) nextCodeInput.focus();
        }
    }

    function removeAlert() {
        if (alertBanner) {
            alertBanner.remove();
        }
    }

    function showAlert(message, type = 'danger') {
        removeAlert();
        if (!form) return;

        const alert = document.createElement('div');
        alert.id = 'po-create-alert';
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        form.parentNode.insertBefore(alert, form);
        alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function updateItemCount() {
        if (!itemCountText || !tbody) return;
        itemCountText.textContent = String(tbody.querySelectorAll('tr').length);
    }

    function validateForm() {
        const rows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];
        if (rows.length === 0) {
            showAlert('Minimal harus ada 1 item.');
            return false;
        }

        const codes = new Set();
        for (const tr of rows) {
            const itemIdInput = tr.querySelector('.item-id-input');
            const codeInput = tr.querySelector('.item-code-input');
            const qtyInput = tr.querySelector('.qty-input');
            const priceInput = tr.querySelector('.price-input');
            const code = String(codeInput.value || '').trim().toUpperCase();
            const qty = parseNumber(qtyInput.value);
            const price = parseNumber(priceInput.value);

            if (!code || !itemIdInput.value) {
                showAlert('Pilih barang yang valid pada setiap baris.');
                codeInput.focus();
                return false;
            }

            if (qty < 0.01) {
                showAlert('Qty harus lebih besar dari 0.');
                qtyInput.focus();
                return false;
            }

            if (price < 0) {
                showAlert('Harga tidak boleh bernilai negatif.');
                priceInput.focus();
                return false;
            }

            if (codes.has(code)) {
                showAlert(`Kode barang "${code}" duplikat.`);
                codeInput.focus();
                return false;
            }
            codes.add(code);
        }

        return true;
    }

    function updateGrandTotal() {
        let total = 0;
        if (!tbody) return;

        tbody.querySelectorAll('tr').forEach(tr => {
            const qty = parseNumber(tr.querySelector('.qty-input').value);
            const price = parseNumber(tr.querySelector('.price-input').value);
            total += qty * price;
        });

        if (grandTotalText) grandTotalText.textContent = formatNumber(total);
        if (grandTotalInput) grandTotalInput.value = total;
    }

    function resolveItemByCode(tr) {
        const codeInput = tr.querySelector('.item-code-input');
        const code = String(codeInput.value || '').trim().toUpperCase();
        codeInput.value = code;

        if (!code) {
            clearItemInfo(tr);
            updateRowSubtotal(tr, updateGrandTotal);
            return;
        }

        if (isDuplicateCode(tbody, code, codeInput)) {
            clearItemInfo(tr, 'Kode duplikat', 'text-danger');
            updateRowSubtotal(tr, updateGrandTotal);
            return;
        }

        const item = itemMap[code];
        if (!item) {
            clearItemInfo(tr, 'Kode tidak ditemukan', 'text-danger');
            updateRowSubtotal(tr, updateGrandTotal);
            return;
        }

        fillItemInfo(tr, item);
        updateRowSubtotal(tr, updateGrandTotal);
    }

    function bindRow(tr) {
        const codeInput = tr.querySelector('.item-code-input');
        const qtyInput = tr.querySelector('.qty-input');
        const priceInput = tr.querySelector('.price-input');

        codeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            if (searchUrl && datalist) {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => loadItemOptions(this.value), 180);
            }
        });

        codeInput.addEventListener('change', () => resolveItemByCode(tr));
        codeInput.addEventListener('blur', () => resolveItemByCode(tr));
        codeInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                resolveItemByCode(tr);
                const rows = tbody.querySelectorAll('tr');
                if (tr === rows[rows.length - 1]) {
                    addRow({});
                }
                moveFocusToNextRow(tr);
            }
        });

        qtyInput.addEventListener('input', () => updateRowSubtotal(tr, updateGrandTotal));
        qtyInput.addEventListener('blur', function() {
            this.value = String(this.value || '').replace(/,/g, '').trim();
        });
        priceInput.addEventListener('input', () => updateRowSubtotal(tr, updateGrandTotal));
        priceInput.addEventListener('blur', function() {
            this.value = String(this.value || '').replace(/,/g, '').trim();
        });

        const remarksToggle = tr.querySelector('.remarks-toggle');
        if (remarksToggle) {
            remarksToggle.addEventListener('click', () => toggleRemarks(tr));
        }

        resolveItemByCode(tr);
        updateRowSubtotal(tr, updateGrandTotal);
    }

    function loadItemOptions(query = '') {
        if (!searchUrl || !datalist) return;

        fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
            headers: { 'Accept': 'application/json' },
        })
            .then(response => response.ok ? response.json() : [])
            .then(results => {
                if (!Array.isArray(results)) return;
                datalist.innerHTML = results.map(item => {
                    const value = String(item.item_code || '').replace(/"/g, '&quot;');
                    const label = [item.item_code, item.item_name, item.unit_name].filter(Boolean).join(' - ');
                    return `<option value="${value}" label="${label.replace(/"/g, '&quot;')}"></option>`;
                }).join('');
            })
            .catch(() => {});
    }

    function addRow(rowData = {}) {
        if (!tbody) return null;
        const idx = tbody.querySelectorAll('tr').length;
        tbody.insertAdjacentHTML('beforeend', rowTemplate(idx, rowData));
        const newRow = tbody.querySelector('tr:last-child');
        bindRow(newRow);
        reindex(tbody);
        updateItemCount();
        updateGrandTotal();
        const qtyInput = newRow.querySelector('.qty-input');
        if (qtyInput) qtyInput.value = String(qtyInput.value || '').replace(/,/g, '').trim();
        const codeInput = newRow.querySelector('.item-code-input');
        if (codeInput) codeInput.focus();
        return newRow;
    }

    function clearRow(tr) {
        const codeInput = tr.querySelector('.item-code-input');
        const qtyInput = tr.querySelector('.qty-input');
        const priceInput = tr.querySelector('.price-input');
        const remarksInput = tr.querySelector('.item-remarks-input');
        if (codeInput) codeInput.value = '';
        if (qtyInput) qtyInput.value = 1;
        if (priceInput) priceInput.value = '';
        if (remarksInput) remarksInput.value = '';
        resolveItemByCode(tr);
    }

    function setSubmitting(isSubmitting) {
        submitLocked = isSubmitting;
        if (!form) return;
        form.querySelectorAll('button[type="submit"]').forEach(button => {
            button.disabled = isSubmitting;
            const label = button.dataset.saveLabel || button.textContent;
            button.innerHTML = isSubmitting
                ? `<i class="fas fa-spinner fa-spin"></i> Menyimpan...`
                : `<i class="fas fa-save"></i> ${label}`;
        });
    }

    if (typeof $.fn !== 'undefined' && $.fn.select2) {
        $('.supplier-select').each(function() {
            if (!$(this).data('select2')) {
                $(this).select2({
                    width: '100%',
                    placeholder: '-- Pilih Supplier --',
                    allowClear: true,
                    dropdownParent: $(document.body),
                });
            }
        });

        $(document).on('select2:select', '.supplier-select', function() {
            const $el = $(this);
            setTimeout(function() {
                const currentField = $el.closest('.po-field');
                const nextField = currentField.nextAll('.po-field').first();
                const nextInput = nextField.find('input, select, textarea').filter(function() {
                    return $(this).is(':visible');
                }).first();

                if (nextInput.length) {
                    nextInput[0].focus();
                    return;
                }

                const firstItemCode = tbody ? tbody.querySelector('.item-code-input') : null;
                if (firstItemCode) {
                    firstItemCode.focus();
                    return;
                }
                if (addBtn) addBtn.focus();
            }, 100);
        });
    }

    if (addBtn) {
        addBtn.addEventListener('click', () => addRow({
            item_code: '',
            item_id: '',
            item_name: '',
            unit_name: '',
            ordered_qty: 1,
            unit_price: '',
            remarks: '',
        }));
    }

    if (tbody) {
        tbody.addEventListener('click', function(e) {
            if (!e.target.closest('.btn-remove')) return;
            const tr = e.target.closest('tr');
            const rows = tbody.querySelectorAll('tr');
            if (rows.length <= 1) {
                clearRow(tr);
                return;
            }
            tr.remove();
            reindex(tbody);
            updateItemCount();
            updateGrandTotal();
        });
    }

    function parseDateInput(value) {
        const raw = String(value ?? '').trim();
        if (!raw) return raw;

        const match = raw.match(/^(\d{1,2})\s*-\s*([A-Za-z]{3})\s*-\s*(\d{2}|\d{4})$/);
        if (!match) return value;

        const day = Number(match[1]);
        const month = {
            jan: 0, feb: 1, mar: 2, apr: 3, may: 4, jun: 5,
            jul: 6, aug: 7, sep: 8, oct: 9, nov: 10, dec: 11,
        }[match[2].toLowerCase()];
        if (month === undefined) return value;

        const year = Number(match[3]);
        const fullYear = year < 100 ? 2000 + year : year;
        const date = new Date(Date.UTC(fullYear, month, day));

        if (
            date.getUTCFullYear() !== fullYear ||
            date.getUTCMonth() !== month ||
            date.getUTCDate() !== day
        ) {
            return value;
        }

        const mm = String(month + 1).padStart(2, '0');
        const dd = String(day).padStart(2, '0');
        return `${fullYear}-${mm}-${dd}`;
    }

    const poDateInput = document.getElementById('poDateInput');
    if (poDateInput) {
        poDateInput.addEventListener('blur', function() {
            const parsed = parseDateInput(this.value);
            if (parsed !== this.value) {
                this.value = parsed;
            }
        });
    }

    function setNotesVisible(isVisible) {
        if (!notesFieldWrapper || !notesToggle) return;

        notesFieldWrapper.style.display = isVisible ? '' : 'none';
        notesToggle.setAttribute('aria-expanded', String(isVisible));
        const label = notesToggle.querySelector('.po-notes-toggle-label');
        if (label) {
            label.textContent = isVisible ? 'Sembunyikan Catatan' : 'Tambahkan Catatan';
        }
        if (isVisible) {
            const notesInput = document.getElementById('notesInput');
            if (notesInput) notesInput.focus();
        }
    }

    if (notesToggle) {
        setNotesVisible(false);
        notesToggle.addEventListener('click', function() {
            setNotesVisible(notesFieldWrapper.style.display === 'none');
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            removeAlert();
            if (submitLocked) return;
            if (poDateInput) {
                poDateInput.value = parseDateInput(poDateInput.value);
            }
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            setSubmitting(true);
        });
    }

    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && String(e.key).toLowerCase() === 's') {
            e.preventDefault();
            if (submitLocked) return;
            if (poDateInput) {
                poDateInput.value = parseDateInput(poDateInput.value);
            }
            if (!validateForm()) return;
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                setSubmitting(true);
                form.submit();
            }
            return;
        }
        if (e.altKey && e.shiftKey && String(e.key).toLowerCase() === 'n') {
            e.preventDefault();
            addRow({
                item_code: '',
                item_id: '',
                item_name: '',
                unit_name: '',
                ordered_qty: 1,
                unit_price: '',
                remarks: '',
            });
        }
    });

    const poNumberInput = document.getElementById('poNumberInput');
    if (poNumberInput) {
        poNumberInput.addEventListener('paste', function(e) {
            setTimeout(() => {
                poNumberInput.value = String(poNumberInput.value || '').trim();
            }, 0);
        });
    }

    const normalizedOldItems = Array.isArray(oldItems) ? oldItems : [];
    if (normalizedOldItems.length > 0) {
        normalizedOldItems.forEach(item => addRow(item));
    } else if (tbody) {
        addRow({});
    }
}

if (typeof window !== 'undefined' && window.PO_CREATE_CONFIG) {
    initPoCreate(window.PO_CREATE_CONFIG);
}
