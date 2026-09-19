export function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

export function parseNumber(value) {
    if (value === null || value === undefined || value === '') return 0;
    return parseFloat(String(value).replace(/,/g, '')) || 0;
}

export function formatNumber(value) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(parseNumber(value));
}

export function createItemMap(items) {
    const map = {};
    items.forEach(item => {
        map[String(item.item_code || '').trim().toUpperCase()] = item;
    });
    return map;
}

export function rowTemplate(idx, rowData = {}) {
    const code = rowData.item_code ?? '';
    const itemId = rowData.item_id ?? '';
    const itemName = rowData.item_name ?? '';
    const unitName = rowData.unit_name ?? '';
    const qty = rowData.ordered_qty ?? 1;
    const unitPrice = rowData.unit_price ?? '';
    const remarks = rowData.remarks ?? '';
    const subtotal = parseNumber(qty) * parseNumber(unitPrice);

    return `
        <tr>
            <td class="row-no row-number">${idx + 1}</td>
            <td>
                <input type="hidden" class="item-id-input" name="items[${idx}][item_id]" value="${escapeHtml(itemId)}">
                <input type="text" class="form-control form-control-sm item-code-input" name="items[${idx}][item_code]" value="${escapeHtml(code)}" autocomplete="off" list="item-codes" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm item-name-display field-readonly" value="${escapeHtml(itemName)}" readonly>
                <div class="remarks-cell">
                    <textarea class="form-control form-control-sm item-remarks-input d-none" name="items[${idx}][remarks]" rows="2">${escapeHtml(remarks)}</textarea>
                    <i class="fas fa-sticky-note remarks-toggle" style="display: ${remarks ? 'inline-block' : 'none'};"></i>
                </div>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm item-unit field-readonly" value="${escapeHtml(unitName)}" readonly>
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" class="form-control form-control-sm qty-input"
                    name="items[${idx}][ordered_qty]" value="${escapeHtml(qty)}" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" class="form-control form-control-sm price-input"
                    name="items[${idx}][unit_price]" value="${escapeHtml(unitPrice)}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm subtotal-display field-readonly"
                    value="${formatNumber(subtotal)}" readonly>
            </td>
            <td>
                <div class="code-status"></div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove btn-action">x</button>
            </td>
        </tr>
    `;
}

export function reindex(tbody) {
    [...tbody.querySelectorAll('tr')].forEach((tr, idx) => {
        tr.querySelector('.row-number').textContent = idx + 1;
        tr.querySelector('.item-id-input').setAttribute('name', `items[${idx}][item_id]`);
        tr.querySelector('.item-code-input').setAttribute('name', `items[${idx}][item_code]`);
        tr.querySelector('.qty-input').setAttribute('name', `items[${idx}][ordered_qty]`);
        tr.querySelector('.price-input').setAttribute('name', `items[${idx}][unit_price]`);
        tr.querySelector('.item-remarks-input').setAttribute('name', `items[${idx}][remarks]`);
    });
}

export function getEnteredCodes(tbody, excludeInput = null) {
    return [...tbody.querySelectorAll('.item-code-input')]
        .filter(input => input !== excludeInput)
        .map(input => String(input.value || '').trim().toUpperCase())
        .filter(Boolean);
}

export function isDuplicateCode(tbody, code, currentInput) {
    if (!code) return false;
    return getEnteredCodes(tbody, currentInput).includes(code);
}

export function clearItemInfo(tr, statusText = '', statusClass = '') {
    tr.querySelector('.item-id-input').value = '';
    tr.querySelector('.item-name-display').value = '';
    tr.querySelector('.item-unit').value = '';

    const statusEl = tr.querySelector('.code-status');
    statusEl.className = 'code-status';
    statusEl.textContent = statusText;

    if (statusClass) {
        statusEl.classList.add(statusClass);
    }
}

export function fillItemInfo(tr, item) {
    tr.querySelector('.item-id-input').value = item.id || '';
    tr.querySelector('.item-name-display').value = item.item_name || '';
    tr.querySelector('.item-unit').value = item.unit_name || '';

    const statusEl = tr.querySelector('.code-status');
    statusEl.className = 'code-status text-success';
    statusEl.textContent = 'Kode valid';
}

export function updateRowSubtotal(tr, updateGrandTotalFn) {
    const qty = parseNumber(tr.querySelector('.qty-input').value);
    const price = parseNumber(tr.querySelector('.price-input').value);
    const subtotal = qty * price;

    tr.querySelector('.subtotal-display').value = formatNumber(subtotal);
    updateGrandTotalFn();
}

export function toggleRemarks(tr) {
    const textarea = tr.querySelector('.item-remarks-input');
    const toggleIcon = tr.querySelector('.remarks-toggle');
    if (!textarea) return;

    const isHidden = textarea.classList.contains('d-none');
    textarea.classList.toggle('d-none', !isHidden);
    if (toggleIcon) {
        toggleIcon.style.display = isHidden ? 'none' : 'inline-block';
    }
}
