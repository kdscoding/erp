@php
    $entity = 'supplier';
    $title = label($entity, 'edit_title', 'Edit Supplier');
    $header = $title;
    $headerSubtitle = label($entity, 'entity.edit_subtitle', 'Perbarui identitas supplier.');
@endphp

<x-ui.page-header
    :entity="$entity"
    :actions="[
        ['label' => 'Kembali', 'url' => route('suppliers.index'), 'class' => 'btn btn-light btn-sm'],
    ]"
/>

<section class="ui-surface">
    <div class="ui-surface-head">
        <div>
            <h3 class="ui-surface-title">Form Supplier</h3>
        </div>
    </div>

    <div class="ui-surface-body">
        <div class="form-wrapper">
            <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="field-label">Kode Supplier</label>
                    <input class="form-control form-control-sm" name="supplier_code"
                        placeholder="Kode unik (contoh: SUP-001)" value="{{ old('supplier_code', $supplier->supplier_code) }}" required>
                </div>

                <div class="form-group">
                    <label class="field-label">Nama Supplier</label>
                    <input class="form-control form-control-sm" name="supplier_name"
                        placeholder="Nama perusahaan supplier" value="{{ old('supplier_name', $supplier->supplier_name) }}" required>
                </div>

                <div class="form-group">
                    <label class="field-label">alamat</label>
                    <input class="form-control form-control-sm" name="address"
                        placeholder="alamat supplier" value="{{ old('address', $supplier->address) }}">
                </div>

                <div class="form-group">
                    <label class="field-label">Telepon</label>
                    <input class="form-control form-control-sm" name="phone"
                        placeholder="Nomor telepon" value="{{ old('phone', $supplier->phone) }}">
                </div>

                <div class="form-group">
                    <label class="field-label">Email</label>
                    <input class="form-control form-control-sm" name="email"
                        placeholder="email@contoh.com" value="{{ old('email', $supplier->email) }}">
                </div>

                <div class="form-group">
                    <label class="field-label">Contact Person</label>
                    <input class="form-control form-control-sm" name="contact_person"
                        placeholder="Nama contact person" value="{{ old('contact_person', $supplier->contact_person) }}">
                </div>

                <div class="form-actions">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm">Batal</a>
                    <button type="submit" class="btn btn-primary btn-sm px-5">Perbarui Supplier</button>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
    .form-wrapper {
        max-width: 540px;
        margin: 24px auto;
        padding: 24px;
    }
    .form-wrapper .form-group {
        margin-bottom: 20px;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid var(--lemon-line, #dfe6b8);
    }
</style>