export function initPoShow(config) {
    const { refreshUrl, csrfToken } = config;
    const $ = window.jQuery;

    initBulkSelect();

    if (typeof $ !== 'undefined') {
        const actionModal = document.getElementById('itemActionModal');
        if (actionModal) {
            const modalEl = actionModal;
            const form = modalEl.querySelector('form');
            const titleEl = modalEl.querySelector('.modal-title');
            const reasonInput = modalEl.querySelector('textarea[name="cancel_reason"]');
            const itemIdInput = modalEl.querySelector('input[name="item_id"]');

            function setCsrf(formEl) {
                if (!formEl) return;
                let tokenInput = formEl.querySelector('input[name="_token"]');
                if (!tokenInput) {
                    tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = '_token';
                    formEl.appendChild(tokenInput);
                }
                tokenInput.value = csrfToken;
            }

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-action]');
                if (!btn) return;

                const action = btn.dataset.action;
                const itemId = btn.dataset.itemId;
                const itemCode = btn.dataset.itemCode;

                if (!itemId) return;

                switch (action) {
                    case 'view-tracking':
                        openTrackingModal(itemId, btn);
                        break;
                    case 'edit-etd':
                        openEtdModal(itemId, btn);
                        break;
                    case 'cancel-item':
                        if (!btn.dataset.cancelUrl) return;
                        if (form && titleEl && reasonInput && itemIdInput) {
                            titleEl.textContent = `Cancel Item ${itemCode}`;
                            form.action = btn.dataset.cancelUrl;
                            setCsrf(form);
                            itemIdInput.value = itemId;
                            reasonInput.value = '';
                        }
                        $(modalEl).modal('show');
                        break;
                    case 'force-close':
                        if (!btn.dataset.forceCloseUrl) return;
                        if (form && titleEl && reasonInput && itemIdInput) {
                            titleEl.textContent = `Force Close Item ${itemCode}`;
                            form.action = btn.dataset.forceCloseUrl;
                            setCsrf(form);
                            itemIdInput.value = itemId;
                            reasonInput.value = '';
                        }
                        $(modalEl).modal('show');
                        break;
                }
            });
        }
    }

    function initBulkSelect() {
        const bulkSelectAll = document.getElementById('bulkSelectAll');
        const bulkCheckboxes = Array.from(document.querySelectorAll('.bulk-item-checkbox'));

        if (bulkSelectAll && bulkCheckboxes.length > 0) {
            bulkSelectAll.addEventListener('change', function() {
                bulkCheckboxes.forEach((checkbox) => {
                    if (!checkbox.disabled) {
                        checkbox.checked = bulkSelectAll.checked;
                    }
                });
            });
        }
    }

    function openTrackingModal(itemId, btn) {
        const modal = document.getElementById('trackingModal');
        if (!modal) return;

        const titleEl = modal.querySelector('.modal-title');
        const bodyEl = modal.querySelector('.modal-body');
        const copyBtn = modal.querySelector('.js-copy-tracking');
        const exportBtn = modal.querySelector('.js-export-tracking');

        const itemCode = btn.dataset.itemCode;
        if (titleEl) {
            titleEl.textContent = `Tracking Shipment / GR ${itemCode}`;
        }

        const hiddenContent = document.querySelector(`.tracking-content[data-item-id="${itemId}"]`);
        if (bodyEl && hiddenContent) {
            bodyEl.innerHTML = hiddenContent.innerHTML;
        } else if (bodyEl) {
            bodyEl.innerHTML = '<div class="small text-muted">Belum ada data tracking untuk item ini.</div>';
        }

        if (copyBtn) {
            copyBtn.onclick = function() {
                const url = btn.dataset.copyUrl;
                if (url) {
                    window.open(url, '_blank');
                }
            };
        }

        if (exportBtn) {
            exportBtn.onclick = function() {
                const url = btn.dataset.excelUrl;
                if (url) {
                    window.open(url, '_blank');
                }
            };
        }

        if (typeof $ !== 'undefined') {
            $(modal).modal('show');
        }
    }

    function openEtdModal(itemId, btn) {
        const modal = document.getElementById('etdModal');
        if (!modal) return;

        const form = modal.querySelector('form');
        const etdInput = modal.querySelector('input[name="etd_date"]');
        const remarksInput = modal.querySelector('textarea[name="remarks"]');

        if (form && btn.dataset.etdUrl) {
            const methodInput = form.querySelector('input[name="_method"]');
            if (!methodInput) {
                const mi = document.createElement('input');
                mi.type = 'hidden';
                mi.name = '_method';
                mi.value = 'PATCH';
                form.appendChild(mi);
            }
            form.action = btn.dataset.etdUrl;
        }

        if (etdInput) {
            etdInput.value = btn.dataset.etdDate || '';
        }

        if (remarksInput) {
            remarksInput.value = btn.dataset.cancelReason || '';
        }

        if (typeof $ !== 'undefined') {
            $(modal).modal('show');
        }
    }

    const refreshBtn = document.getElementById('refreshStatusBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.location.href = refreshUrl;
        });
    }
}

if (typeof window !== 'undefined' && window.PO_SHOW_CONFIG) {
    initPoShow(window.PO_SHOW_CONFIG);
}
