<?php($title = 'Settings')
@php($header = 'System Settings')
@php($headerSubtitle = 'Parameter sistem dan document terms yang memengaruhi tampilan serta perilaku operasional.')

@section('content')
    <div class="page-shell">
        <section class="page-head">
            <div class="page-head-main">
                <h2 class="page-section-title">System Parameters</h2>
                <p class="page-section-subtitle">Pengaturan utama sistem dan label document terms dikelola di satu halaman administrasi.</p>
            </div>

            <div class="page-actions">
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-light">Daftar User</a>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">General Settings</h3>
                    <div class="ui-surface-subtitle">Parameter sederhana seperti izin over receipt ditempatkan di surface terpisah.</div>
                </div>
            </div>
            <div class="ui-surface-body">
                <div class="alert alert-info">
                    Pengaturan user sekarang dikelola terpisah melalui menu
                    <a href="{{ route('users.index') }}">Daftar User</a>.
                </div>

                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" value="1" name="allow_over_receipt" id="allow_over_receipt" {{ $allowOver == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_over_receipt">Izinkan Over Receipt</label>
                    </div>
                    <button class="btn btn-primary btn-sm">Simpan</button>
                </form>
            </div>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Document Terms</h3>
                    <div class="ui-surface-subtitle">Ubah label tampilan status tanpa mengubah kode internal flow sistem.</div>
                </div>
            </div>
            <div class="ui-surface-body">
                <div class="alert alert-light border">
                    Ubah istilah tampilan status dari UI. Kode internal tetap sama, jadi flow sistem tidak berubah walaupun label display diganti.
                    @if (!empty($hasBadgeColumns) && $hasBadgeColumns)
                        <hr class="my-2">
                        <div class="mb-0">Badge juga sekarang dikendalikan dari database: <code>badge_class</code> untuk warna badge dan <code>badge_text</code> untuk warna teks badge.</div>
                    @endif
                    <hr class="my-2">
                    <div class="mb-0">
                        Arsitektur baru:
                        <code>internal_code</code> adalah identitas domain yang stabil,
                        <code>code</code> adalah legacy term untuk kompatibilitas,
                        dan <code>label</code> adalah bahasa tampilan di UI.
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.document-terms.update') }}">
                    @csrf

                    @if (!empty($documentTermGroups) && count($documentTermGroups) > 0)
                        @foreach ($documentTermGroups as $groupKey => $terms)
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <div class="font-weight-bold text-uppercase">{{ str_replace('_', ' ', $groupKey) }}</div>
                                    <div class="small text-muted">{{ count($terms) }} term</div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0 ui-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 180px;">Internal Code</th>
                                                <th style="width: 180px;">Code</th>
                                                <th style="min-width: 220px;">Label</th>
                                                <th style="min-width: 220px;">Description</th>
                                                <th style="min-width: 80px;">Sort</th>
                                                <th style="width: 100px;">Aktif</th>
                                                @if (!empty($hasBadgeColumns) && $hasBadgeColumns)
                                                    <th style="min-width: 200px;">Badge Class</th>
                                                    <th style="min-width: 180px;">Badge Text</th>
                                                    <th style="min-width: 150px;">Preview</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($terms as $term)
                                                @php
                                                    $labelValue = old('document_terms.' . $term->id . '.label', $term->label);
                                                    $descValue = old('document_terms.' . $term->id . '.description', $term->description);
                                                    $sortValue = old('document_terms.' . $term->id . '.sort_order', $term->sort_order);
                                                    $activeValue = old('document_terms.' . $term->id . '.is_active', $term->is_active);
                                                    $badgeClassValue = old('document_terms.' . $term->id . '.badge_class', isset($term->badge_class) ? $term->badge_class : 'bg-secondary');
                                                    $badgeTextValue = old('document_terms.' . $term->id . '.badge_text', isset($term->badge_text) ? $term->badge_text : 'text-white');
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="doc-number"><?php echo e($term->internal_code ?: '-'); ?></div>
                                                        <div class="doc-meta">Stable domain key</div>
                                                    </td>
                                                    <td>
                                                        <div class="doc-number"><?php echo e($term->code); ?></div>
                                                        <div class="doc-meta">ID: <?php echo e($term->id); ?></div>
                                                        <div class="doc-meta">Group: <?php echo e($term->group_key); ?></div>
                                                    </td>
                                                    <td><input type="text" name="document_terms[<?php echo e($term->id); ?>][label]" value="<?php echo e($labelValue); ?>" class="form-control form-control-sm" required></td>
                                                    <td><input type="text" name="document_terms[<?php echo e($term->id); ?>][description]" value="<?php echo e($descValue); ?>" class="form-control form-control-sm"></td>
                                                    <td><input type="number" min="0" max="9999" name="document_terms[<?php echo e($term->id); ?>][sort_order]" value="<?php echo e($sortValue); ?>" class="form-control form-control-sm" required></td>
                                                    <td class="text-center"><input type="checkbox" name="document_terms[<?php echo e($term->id); ?>][is_active]" value="1" <?php echo e($activeValue ? 'checked' : ''); ?>></td>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($hasBadgeColumns) && $hasBadgeColumns): ?>
                                                        <td><input type="text" name="document_terms[<?php echo e($term->id); ?>][badge_class]" value="<?php echo e($badgeClassValue); ?>" class="form-control form-control-sm" placeholder="bg-success / bg-danger / bg-warning"></td>
                                                        <td><input type="text" name="document_terms[<?php echo e($term->id); ?>][badge_text]" value="<?php echo e($badgeTextValue); ?>" class="form-control form-control-sm" placeholder="text-white / text-dark"></td>
                                                        <td><span class="badge <?php echo e($badgeClassValue); ?> <?php echo e($badgeTextValue); ?>"><?php echo e($labelValue); ?></span></td>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </tr>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php else: ?>
                        <div class="text-muted">Belum ada document terms.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary btn-sm">Simpan Document Terms</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views/settings/index.blade.php ENDPATH**/ ?>