# Prompt: Comprehensive UI/UX Consistency & Data Integrity Implementation Plan

## Context
You are a senior software architect tasked with creating a technical implementation plan for an ERP application (Laravel + Blade) to ensure **UI/UX consistency** and **data integrity** across 7 modules by synchronizing all labeling elements (table column headers, field titles, data labels, validation messages, status labels).

### Application Stack
- **Backend**: Laravel 10+ (PHP 8.2+)
- **Frontend**: Blade templates, vanilla JS, Bootstrap 5
- **Database**: MySQL/PostgreSQL
- **Current State**: Labels hardcoded in views, controllers, and validation messages; partial centralization via `TermCatalog` (document_terms table) and `DocumentTermCodes` constants

---

## Modules to Synchronize
1. **Supplier Management** (`suppliers.*` routes/views)
2. **Item/Product Catalog** (`masters.items.*` routes/views)
3. **Category Management** (`masters.item-categories.*` routes/views)
4. **Unit of Measure (UOM)** (`masters.units.*` routes/views)
4. **Purchase Order (PO)** (`po.*` routes/views)
5. **Order Tracking** (`tracking.index` route/view)
6. **Shipment Management** (`shipments.*` routes/views)

---

## Current Pain Points (from codebase analysis)
- **Duplicated labels**: "Kode", "Nama", "Status", "Aksi", "Terakhir Diubah" repeated across 7+ view files
- **Inconsistent field naming**: `supplier_code` vs `item_code` vs `category_code` vs `unit_code` — same concept, different labels
- **Validation messages**: Inline Indonesian strings in controllers, not reusable
- **Status labels**: Mixed between `TermCatalog` (DB-driven) and `DocumentTermCodes` (constants) and hardcoded in views
- **Table headers**: No single source of truth for column headers
- **Form labels**: Duplicated in create/edit blades per module

---

## Required Deliverables

### 1. Centralized Label Schema (Single Source of Truth)
Create a **PHP-based label registry** that defines:
- **Entity-level labels**: Singular/plural names, module titles
- **Field-level labels**: Display name, placeholder, help text, validation messages per field
- **Table column headers**: Per entity, per view context (index, detail, export)
- **Status/Enum labels**: Unified with `TermCatalog` and `DocumentTermCodes`
- **Action labels**: Button text, link text, dropdown items

**Structure example**:
```php
// app/Support/LabelRegistry.php
return [
    'supplier' => [
        'entity' => ['singular' => 'Supplier', 'plural' => 'Suppliers'],
        'fields' => [
            'supplier_code' => ['label' => 'Kode Supplier', 'placeholder' => 'Kode unik', 'validation' => [...]],
            'supplier_name' => ['label' => 'Nama Supplier', 'placeholder' => 'Nama perusahaan', 'validation' => [...]],
            'status' => ['label' => 'Status', 'options' => ['active' => 'Aktif', 'inactive' => 'Nonaktif']],
        ],
        'table_columns' => ['index' => ['supplier_code' => 'Kode', 'supplier_name' => 'Nama Supplier', ...]],
        'actions' => ['create' => 'Tambah Supplier', 'edit' => 'Edit', 'delete' => 'Hapus'],
    ],
    // ... repeat for item, category, unit, po, shipment, tracking
];
```

### 2. Shared Blade Components (Component Library)
Extract **reusable Blade components** to eliminate duplication:
- `<x-data-table :columns="..." :rows="..." />` — renders consistent tables with sorting, empty states, pagination
- `<x-form-field :field="..." />` — renders label + input + error + help text from schema
- `<x-filter-bar :fields="..." />` — consistent filter UI across modules
- `<x-status-badge :status="..." :scope="..." />` — already exists, ensure it uses registry
- `<x-page-header :title="..." :subtitle="..." :actions="..." />`
- `<x-empty-state :icon="..." :title="..." :subtitle="..." :action="..." />`
- `<x-fab :route="..." :label="..." />`

### 3. Controller Base Class / Traits
- `HasUnifiedLabels` trait: injects label registry into views automatically
- `ValidatesWithRegistry` trait: pulls validation rules/messages from registry
- `FiltersWithRegistry` trait: builds filter forms from schema

### 4. Migration Strategy (Zero-Downtime)
1. **Phase 1**: Create `LabelRegistry` class with all current labels extracted from views/controllers
2. **Phase 2**: Build shared Blade components
3. **Phase 3**: Refactor one module (e.g., Suppliers) as pilot — replace hardcoded labels with registry + components
4. **Phase 4**: Apply to remaining 6 modules incrementally
5. **Phase 5**: Add tests (snapshot tests for label output, integration tests for validation messages)

### 5. Developer Experience (DX) Tooling
- Artisan command: `php artisan labels:audit` — scans views/controllers for hardcoded strings not in registry
- Artisan command: `php artisan labels:sync` — exports registry to JSON for frontend (JS) consumption
- IDE helper: PHPDoc `@label` annotations for static analysis

---

## Technical Requirements

### LabelRegistry Implementation
- **Class**: `App\Support\LabelRegistry` (singleton, cached)
- **Data source**: PHP array files in `resources/labels/*.php` (one per module) for version control diffability
- **Fallback**: If key missing, log warning + return key as last resort
- **Localization-ready**: Structure supports `en`/`id` keys for future i18n

### Blade Component Contracts
```blade
{{-- resources/views/components/data-table.blade.php --}}
@props(['columns', 'rows', 'emptyState', 'sortable', 'exportable'])
<table class="table table-hover ui-table">
    <thead>
        <tr>
            @foreach($columns as $key => $col)
                <th {{ $col['sortable'] ?? false ? 'data-sortable' : '' }}>
                    {{ $col['label'] ?? \App\Support\LabelRegistry::column($entity, $key) }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($columns as $key => $col)
                    <td>{!! $col['render'] ?? ($row->{$key} ?? '-') !!}</td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ count($columns) }}"><x-empty-state ... /></td></tr>
        @endforelse
    </tbody>
</table>
```

### Validation Message Centralization
Move all validation messages from controllers to registry:
```php
// resources/labels/validation.php
return [
    'supplier' => [
        'supplier_code.required' => 'Kode supplier wajib diisi.',
        'supplier_code.unique' => 'Kode supplier sudah digunakan.',
        'supplier_name.required' => 'Nama supplier wajib diisi.',
    ],
    // shared rules
    'shared' => [
        'code.required' => 'Kode :field wajib diisi.',
        'code.unique' => 'Kode :field sudah digunakan.',
        'name.required' => 'Nama :field wajib diisi.',
    ],
];
```

---

## Acceptance Criteria
1. **Zero hardcoded labels** in views/controllers for the 7 modules (audit passes)
2. **Single source of truth**: All labels defined in `resources/labels/*.php`
3. **Consistent UX**: Same field → same label everywhere (table header, form label, validation message, export column)
4. **DRY components**: Table, form, filter, empty state rendered via shared components
5. **Test coverage**: Snapshot tests for label output; mutation tests for registry fallback
6. **Documentation**: `docs/LABEL_REGISTRY.md` with contribution guide

---

## Output Format
Generate a **detailed implementation plan** as a Markdown document with:
1. **Architecture diagram** (Mermaid) showing registry → components → views flow
2. **File structure** for new files (`resources/labels/`, `app/Support/LabelRegistry.php`, components)
3. **Phase-by-phase task breakdown** with effort estimates (S/M/L)
4. **Code samples** for registry, 2-3 key components, controller trait
5. **Migration checklist** per module
6. **Risk mitigation** (performance, caching, backward compatibility)
7. **Rollback plan** per phase

---

## Constraints
- **No breaking changes** to existing routes/APIs
- **No new external dependencies** (use Laravel built-ins)
- **Performance**: Registry must be cached (config cache or Redis)
- **Backward compatible**: Existing views work during migration
- **Indonesian language** primary; structure ready for English

---

## Reference Files (for context)
- `app/Support/TermCatalog.php` — existing DB-driven label system
- `app/Support/DocumentTermCodes.php` — existing status constants
- `resources/views/suppliers/index.blade.php` — example of duplicated table headers/styles
- `resources/views/masters/items/index.blade.php` — same patterns, different labels
- `resources/views/po/index.blade.php` — different UI pattern, same concepts
- `resources/views/shipments/index.blade.php` — most complex, multiple tabs
- `resources/views/tracking.blade.php` — tracking-specific columns

---

**Generate the implementation plan now.**