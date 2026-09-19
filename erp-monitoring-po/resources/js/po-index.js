export function initPoIndex(config) {
    const { searchPlaceholder = 'Cari PO...' } = config || {};
    const $ = window.jQuery;

    if (typeof $.fn !== 'undefined' && $.fn.select2) {
        $('.supplier-select').each(function() {
            if (!$(this).data('select2')) {
                $(this).select2({
                    width: '100%',
                    placeholder: 'Semua Supplier',
                    allowClear: true,
                    dropdownParent: $(document.body)
                });
            }
        });
    }

    const searchInput = document.getElementById('po-table-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = String(this.value || '').toLowerCase().trim();
            const tbody = document.querySelector('.ui-table tbody');
            if (!tbody) return;

            tbody.querySelectorAll('tr').forEach(tr => {
                const poNumber = tr.querySelector('td .doc-number')?.textContent?.toLowerCase() || '';
                const supplierCode = tr.querySelector('td:nth-child(3) .doc-number')?.textContent?.toLowerCase() || '';
                const supplierName = tr.querySelector('td:nth-child(3) .doc-meta')?.textContent?.toLowerCase() || '';

                const match = !term || poNumber.includes(term) || supplierCode.includes(term) || supplierName.includes(term);
                tr.style.display = match ? '' : 'none';
            });
        });
    }
}

if (typeof window !== 'undefined' && window.PO_INDEX_CONFIG) {
    initPoIndex(window.PO_INDEX_CONFIG || {});
}