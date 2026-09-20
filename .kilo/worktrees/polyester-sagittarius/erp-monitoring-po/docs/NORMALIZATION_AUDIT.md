# Normalization Audit

## Current state
- `users`, `roles`, `user_roles`, `suppliers`, `units`, `plants`, `warehouses`, `items` are properly separated as master/reference tables.
- `document_terms` is now the master source for display labels of statuses and business terms.
- Transaction tables still store stable status codes as strings:
  - `purchase_orders.status`
  - `purchase_order_items.item_status`
  - `shipments.status`
  - `goods_receipts.status`

## Architectural decision on primary keys vs business codes

- Surrogate key `id` is still retained as the main relational key for transactional safety.
- Business codes such as `supplier_code`, `item_code`, `unit_code`, `warehouse_code`, and `plant_code` remain unique identifiers for human-facing lookup and interoperability.
- They are **not** promoted to primary relational FK by default because, in this repo, those codes may still be edited through admin/master-data flow and historical transactions must remain stable.

Decision:

- keep `id` as FK on transaction tables
- keep business code as alternate unique key
- improve readability through UI, exports, and query layer rather than replacing every FK with string code

## What is normalized enough
- Master/reference data
- Role assignment
- Display terminology via `document_terms`
- Shipment and goods receipt line items

## What is still transitional
- Status codes are not foreign keys yet.
- `po_status_histories` keeps snapshot strings (`from_status`, `to_status`) by design, so historical records remain readable even if term labels change later.
- Some old schema columns still reflect historical plans and need explicit cleanup migration instead of silent retention.

## Cleanup completed in current refactor wave

- `goods_receipt_items.item_id` is being removed because it duplicates `purchase_order_items.item_id`.
- dormant PO header fields planned but not used in active flow are being removed:
  - `sent_to_supplier_at`
  - `approved_by`
  - `approved_at`
  - `bc_reference_no`
  - `bc_reference_date`

Reason:

- they are not part of the active simplified monitoring model
- they create ambiguity in schema reading
- they add maintenance burden without supporting the current workflow

## Recommended next phases
1. Keep stable codes in transaction tables, but centralize every status filter/badge/form option through `document_terms`.
2. If stricter normalization is desired, introduce dedicated `status_code` references or a validated enum layer for each module.
3. Only after UI/report/query usage is clean, consider foreign-key-like status master references.
4. Do not replace all master `id` keys with business codes unless codes are declared immutable and every transaction/import/export path is redesigned first.

## Demo data reset
- Full rebuild: `php artisan migrate:fresh --seed --force`
- Demo transaction reset only: `php artisan erp:reset-demo`
