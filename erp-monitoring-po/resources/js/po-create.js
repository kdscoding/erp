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

export function initPoCreate(config) {
    const { items, oldItems, searchUrl } = config;
    const $ = window.jQuery;
    const tbody = document.querySelector('#po-items-table tbody');
    const addBtn = document.getElementById('btn-add-item');
    const grandTotalText = document.getElementById('grand-total-text');
    const grandTotalInput = document.getElementById('grand-total-input');
    const form = document.getElementById('po-form');

    const alertBanner = document.getElementById('po-create-alert');

    const itemMap = createItemMap(items);

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
        alert.style.display = 'block';
        alert.textContent = message;
        form.parentNode.insertBefore(alert, form);
    }

    function validateForm() {
        const rows = tbody.querySelectorAll('tr');
        if (rows.length === 0) {
            showAlert('Minimal harus ada 1 item.');
            return false;
        }

        const codes = new Set();
        for (const tr of rows) {
            const itemIdInput = tr.querySelector('.item-id-input');
            const code = String(tr.querySelector('.item-code-input').value || '').trim().toUpperCase();
            const qty = parseNumber(tr.querySelector('.qty-input').value);

            if (code && !itemIdInput.value) {
                showAlert(`Kode barang "${code}" tidak valid atau tidak ditemukan.`);
                return false;
            }

            if (qty < 0.01) {
                showAlert('Qty harus lebih besar dari 0.');
                return false;
            }

            if (code) {
                if (codes.has(code)) {
                    showAlert(`Kode barang "${code}" duplikat.`);
                    return false;
                }
                codes.add(code);
            }
        }

        return true;
    }

    function updateGrandTotal() {
        let total = 0;

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
            clearItemInfo(tr, '');
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
        });

        codeInput.addEventListener('change', function() {
            resolveItemByCode(tr);
        });

        codeInput.addEventListener('blur', function() {
            resolveItemByCode(tr);
        });

        codeInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                resolveItemByCode(tr);
                const rows = tbody.querySelectorAll('tr');
                if (tr === rows[rows.length - 1]) {
                    e.preventDefault();
                    addRow({});
                }
            }
        });

        qtyInput.addEventListener('input', function() {
            updateRowSubtotal(tr, updateGrandTotal);
        });

        priceInput.addEventListener('input', function() {
            updateRowSubtotal(tr, updateGrandTotal);
        });

        const remarksToggle = tr.querySelector('.remarks-toggle');
        if (remarksToggle) {
            remarksToggle.addEventListener('click', function() {
                toggleRemarks(tr);
            });
        }

        resolveItemByCode(tr);
        updateRowSubtotal(tr, updateGrandTotal);
    }

    function addRow(rowData = {}) {
        const idx = tbody.querySelectorAll('tr').length;
        tbody.insertAdjacentHTML('beforeend', rowTemplate(idx, rowData));
        const newRow = tbody.querySelector('tr:last-child');
        bindRow(newRow);
        reindex(tbody);
        updateGrandTotal();
    }

    if (typeof $.fn !== 'undefined' && $.fn.select2) {
        $('.supplier-select').each(function() {
            if (!$(this).data('select2')) {
                $(this).select2({
                    width: '100%',
                    placeholder: '-- Pilih Supplier --',
                    allowClear: true,
                    dropdownParent: $(document.body)
                });
            }
        });
    }

    if (addBtn) {
        addBtn.addEventListener('click', function() {
            addRow({
                item_code: '',
                item_id: '',
                item_name: '',
                unit_name: '',
                ordered_qty: 1,
                unit_price: '',
                remarks: ''
            });
        });
    }

    if (tbody) {
        tbody.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove')) {
                const rows = tbody.querySelectorAll('tr');
                if (rows.length <= 1) return;

                e.target.closest('tr').remove();
                reindex(tbody);
                updateGrandTotal();
            }
        });
    }

    if (form) {
        const submitButtons = Array.from(form.querySelectorAll('button[type="submit"]'));

        form.addEventListener('submit', function(e) {
            removeAlert();
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            submitButtons.forEach(btn => {
                if (window.jQuery) {
                    window.jQuery(btn).prop('disabled', true);
                } else {
                    btn.disabled = true;
                }
            });
        });
    }

    if (form) {
        form.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && String(e.key).toLowerCase() === 's') {
                e.preventDefault();
                if (validateForm()) {
                    form.submit();
                }
            }
            if ((e.ctrlKey || e.metaKey) && String(e.key).toLowerCase() === 'n') {
                e.preventDefault();
                addRow({
                    item_code: '',
                    item_id: '',
                    item_name: '',
                    unit_name: '',
                    ordered_qty: 1,
                    unit_price: '',
                    remarks: ''
                });
            }
        });
    }

    if (oldItems && oldItems.length > 0) {
        oldItems.forEach(item => addRow(item));
    } else if (tbody) {
        addRow({});
    }
}

if (typeof window !== 'undefined' && window.PO_CREATE_CONFIG) {
    initPoCreate(window.PO_CREATE_CONFIG);
}
