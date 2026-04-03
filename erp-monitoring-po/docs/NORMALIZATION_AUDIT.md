# Normalization Audit

## Current state
- `users`, `roles`, `user_roles`, `suppliers`, `units`, `plants`, `warehouses`, `items` are properly separated as master/reference tables.
- `document_terms` is now the master source for display labels of statuses and business terms.
- Transaction tables still store stable status codes as strings:
  - `purchase_orders.status`
  - `purchase_order_items.item_status`
  - `shipments.status`
  - `goods_receipts.status`

## What is normalized enough
- Master/reference data
- Role assignment
- Display terminology via `document_terms`
- Shipment and goods receipt line items

## What is still transitional
- Status codes are not foreign keys yet.
- `po_status_histories` keeps snapshot strings (`from_status`, `to_status`) by design, so historical records remain readable even if term labels change later.

## Recommended next phases
1. Keep stable codes in transaction tables, but centralize every status filter/badge/form option through `document_terms`.
2. If stricter normalization is desired, introduce dedicated `status_code` references or a validated enum layer for each module.
3. Only after UI/report/query usage is clean, consider foreign-key-like status master references.

## Demo data reset
- Full rebuild: `php artisan migrate:fresh --seed --force`
- Demo transaction reset only: `php artisan erp:reset-demo`
